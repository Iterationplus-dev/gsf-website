#!/usr/bin/env bash
#
# Production file permissions for a cPanel account.
#
#     bash tools/set-permissions.sh [application-root] [document-root]
#
# Run from cPanel -> Terminal, or over SSH, after extracting or replacing the
# application. With no arguments it works on the directory containing this
# script's parent, which is correct when the archive was extracted whole.
#
# On cPanel, PHP and Apache both run as the account's own user, so the owner
# needs write access and nobody else needs any. That is 755 on directories and
# 644 on files: the world can never write, and 777 appears nowhere.
#
#   directories   755  rwxr-xr-x   owner writes, others traverse and read
#   files         644  rw-r--r--   owner writes, others read
#   artisan       755              executable
#   vendor/bin/*  755              executable
#   .env          600  rw-------   owner only; nothing else may read secrets
#
# storage/ and bootstrap/cache/ need no special treatment: at 755 the owner —
# which is the user PHP runs as — can already write to them.

set -euo pipefail

app_root="${1:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}"
doc_root="${2:-}"

fail() { printf '  !! %s\n' "$1" >&2; }

if [ ! -f "$app_root/artisan" ] || [ ! -d "$app_root/bootstrap" ]; then
    fail "$app_root does not look like the application root (no artisan)."
    fail "Pass it explicitly: bash tools/set-permissions.sh /home/USER/gsf"
    exit 1
fi

printf 'Applying production permissions\n'
printf '  application : %s\n' "$app_root"
[ -n "$doc_root" ] && printf '  document    : %s\n' "$doc_root"
printf '\n'

# --- The application ---------------------------------------------------------
# node_modules is excluded because it should not be on the server at all; if it
# was uploaded by accident, walking it costs minutes and changes nothing.
printf '  directories to 755 ... '
find "$app_root" -path "$app_root/node_modules" -prune -o -type d -exec chmod 755 {} +
printf 'done\n'

printf '  files to 644 ....... '
find "$app_root" -path "$app_root/node_modules" -prune -o -type f -exec chmod 644 {} +
printf 'done\n'

printf '  executables ........ '
chmod 755 "$app_root/artisan"
if [ -d "$app_root/vendor/bin" ]; then
    find "$app_root/vendor/bin" -type f -exec chmod 755 {} +
fi
printf 'done\n'

# --- Secrets -----------------------------------------------------------------
if [ -f "$app_root/.env" ]; then
    chmod 600 "$app_root/.env"
    printf '  .env to 600 ........ done\n'
else
    fail ".env is missing — copy production.env.template to .env first."
fi

# --- Split document root -----------------------------------------------------
# Only the contents are touched. The document root directory itself is left
# exactly as cPanel created it, because some accounts require 750 on
# public_html and loosening it breaks the account rather than fixing anything.
if [ -n "$doc_root" ] && [ -d "$doc_root" ]; then
    printf '  document root ...... '
    find "$doc_root" -mindepth 1 -type d -exec chmod 755 {} +
    find "$doc_root" -mindepth 1 -type f -exec chmod 644 {} +
    printf 'done\n'
fi

# --- Verification ------------------------------------------------------------
# Permissions that look right can still be wrong. These are the two failures
# that actually take the site down: a storage directory the application cannot
# write to, and a vendor file it cannot read.
printf '\nVerifying\n'
status=0

for dir in storage storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache; do
    path="$app_root/$dir"

    if [ ! -d "$path" ]; then
        fail "$dir is missing"
        status=1
    elif [ ! -w "$path" ]; then
        fail "$dir is not writable"
        status=1
    else
        printf '  %-34s writable\n' "$dir"
    fi
done

unreadable=$(find "$app_root/vendor" -type f ! -readable 2>/dev/null | head -5 || true)

if [ -n "$unreadable" ]; then
    fail 'files in vendor/ cannot be read — this is the empty 500 with no log:'
    printf '     %s\n' $unreadable
    status=1
else
    printf '  %-34s readable\n' 'vendor/'
fi

writable=$(find "$app_root" -path "$app_root/node_modules" -prune -o -perm -o+w -print 2>/dev/null | head -5 || true)

if [ -n "$writable" ]; then
    fail 'world-writable paths remain:'
    printf '     %s\n' $writable
    status=1
else
    printf '  %-34s none\n' 'world-writable paths'
fi

printf '\n'
if [ "$status" -eq 0 ]; then
    printf 'Permissions are correct.\n'
else
    printf 'Finished with problems — see the lines marked !! above.\n'
fi

exit "$status"
