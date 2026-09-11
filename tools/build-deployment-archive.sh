#!/usr/bin/env bash
#
# Build the cPanel deployment archive.
#
#     bash tools/build-deployment-archive.sh
#
# Produces deploy/gsf-website-<date>.tar.gz containing a single gsf-website/
# directory: the application, a production-only vendor/, and the built Vite
# assets. Never the .env, never node_modules, never the legacy backup.
#
# Why .tar.gz and not .zip
# ------------------------
# A zip written on Windows carries no Unix permission bits. Extracting one on a
# Linux host leaves every file at whatever the extractor decides, which is how a
# previous deployment ended up with unreadable files under vendor/ — an empty
# 500 with nothing in any Laravel log, because the failure happens before
# Laravel can register an error handler. A tar stores the mode of every entry,
# and the modes are normalised as the archive is written, so what arrives on the
# server is already correct: 755 on directories, 644 on files, 777 nowhere.
#
# cPanel's File Manager extracts .tar.gz as readily as .zip.

set -euo pipefail

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
stamp="$(date +%Y%m%d-%H%M)"
stage="$root/deploy/gsf-website"
archive="$root/deploy/gsf-website-$stamp.tar.gz"

step() { printf '\n==> %s\n' "$1"; }
fail() { printf '  !! %s\n' "$1" >&2; exit 1; }

# Everything the server does not need: the toolchain, the tests, the agent and
# editor files, the legacy backup, and anything carrying a secret.
EXCLUDES=(
    --exclude=./.git
    --exclude=./.github
    --exclude=./.claude
    --exclude=./.editorconfig
    --exclude=./.env
    --exclude=./.env.backup
    --exclude=./.env.production
    --exclude=./auth.json
    --exclude=./backup
    --exclude=./deploy
    --exclude=./node_modules
    --exclude=./scaffold
    --exclude=./tests
    --exclude=./tools
    --exclude=./vendor
    --exclude=./AGENTS.md
    --exclude=./CLAUDE.md
    --exclude=./boost.json
    --exclude=./phpunit.xml
    --exclude=./.phpunit.result.cache
    --exclude=./.phpunit.cache
    --exclude=./storage/logs/*
    --exclude=./storage/framework/cache/data/*
    --exclude=./storage/framework/sessions/*
    --exclude=./storage/framework/views/*
    --exclude=./storage/framework/testing/*
    --exclude=./public/storage
    --exclude=./public/hot
)

step 'Checking prerequisites'
for tool in composer npm tar; do
    command -v "$tool" >/dev/null || fail "$tool is not on PATH"
done
printf '  composer, npm and tar present\n'

step 'Building frontend assets'
# resources/css/app.css lists storage/framework/views as a Tailwind @source, so
# the compiled Blade output has to exist before Vite runs. After an
# `optimize:clear` that directory is empty, and the build then silently produces
# a stylesheet missing every rule only those files mention — a smaller CSS file
# and a subtly broken site. Warming the view cache first makes that impossible.
(cd "$root" && php artisan view:cache >/dev/null)
compiled="$(find "$root/storage/framework/views" -name '*.php' | wc -l)"
[ "$compiled" -gt 100 ] || fail "only $compiled compiled views — Tailwind would scan too little"
printf '  %s Blade templates compiled for Tailwind to scan\n' "$compiled"

(cd "$root" && npm run build >/dev/null)
[ -f "$root/public/build/manifest.json" ] || fail 'public/build/manifest.json was not produced'

css="$(find "$root/public/build/assets" -name 'app-*.css' -print -quit)"
bytes="$(wc -c < "$css")"
[ "$bytes" -gt 60000 ] || fail "$(basename "$css") is only $bytes bytes — the stylesheet is incomplete"
printf '  %s (%s bytes)\n' "$(basename "$css")" "$bytes"

step 'Staging the application'
rm -rf "$stage"
mkdir -p "$stage"

tar --create --directory="$root" "${EXCLUDES[@]}" . | tar --extract --directory="$stage"
printf '  application files staged\n'

step 'Installing production dependencies'
# --no-dev keeps the test and formatting toolchain off the server. The platform
# pin in composer.json holds resolution to PHP 8.3, so packages needing a newer
# runtime than the server has are never selected.
(cd "$stage" && composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-progress 2>&1 | tail -3)

[ -f "$stage/vendor/autoload.php" ] || fail 'vendor/autoload.php is missing'

# Package discovery runs as part of the install and writes its manifests here,
# as would a config or route cache built on this machine — and those record
# absolute local paths. None of it may travel: the server rebuilds all of it
# against its own layout during the post-deployment cache step.
find "$stage/bootstrap/cache" -type f ! -name '.gitignore' -delete
printf '  bootstrap/cache emptied for the server to rebuild
'

step 'Adding deployment helpers'
cp "$root/tools/php-check.php" "$stage/php-check.php"
cp "$root/tools/fix-permissions.php" "$stage/fix-permissions.php"
cp "$root/tools/set-permissions.sh" "$stage/set-permissions.sh"
cp "$root/docs/deployment-cpanel.md" "$stage/DEPLOY-CPANEL.md"
cp "$root/docs/production.env.template" "$stage/production.env.template"
printf '  php-check.php, fix-permissions.php, set-permissions.sh,\n'
printf '  DEPLOY-CPANEL.md, production.env.template\n'

step 'Checking nothing secret is going out'
for forbidden in .env .env.backup .env.production auth.json backup node_modules tests; do
    [ -e "$stage/$forbidden" ] && fail "$forbidden reached the staging directory"
done
printf '  no environment file, credentials or legacy backup present\n'

step 'Writing the archive'
# --mode normalises every entry as it is written. u=rwX gives the owner read and
# write, and execute only where execute already made sense — directories, and
# files that already carried the bit. go=rX gives everyone else read and
# traverse, and never write.
rm -f "$archive"
tar --create --gzip \
    --file="$archive" \
    --directory="$root/deploy" \
    --mode='u=rwX,go=rX' \
    --owner=0 --group=0 \
    gsf-website

printf '  %s\n' "$archive"
printf '  %s compressed\n' "$(du -h "$archive" | cut -f1)"

step 'Verifying the recorded permissions'
# The first field of a verbose listing is the mode. Nothing may be writable by
# the group or by the world, and every distinct mode is printed so that an
# unexpected one is visible rather than merely absent from a pass/fail line.
listing="$(tar --list --verbose --file="$archive")"

printf '%s\n' "$listing" | awk '{print $1}' | sort | uniq -c | sed 's/^/  /'

loose="$(printf '%s\n' "$listing" | awk 'substr($1,6,1)=="w" || substr($1,9,1)=="w" {print $NF}' | head -5)"

if [ -n "$loose" ]; then
    printf '  !! group- or world-writable entries recorded:\n' >&2
    printf '     %s\n' $loose >&2
    fail 'do not deploy this archive'
fi

printf '  nothing group- or world-writable\n'
printf '\nDone. Upload %s and follow DEPLOY-CPANEL.md.\n' "$(basename "$archive")"
