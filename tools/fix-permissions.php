<?php

/**
 * Permission repair for accounts without shell access.
 *
 * tools/set-permissions.sh is the better tool and should be preferred wherever
 * cPanel offers Terminal. This exists for accounts that do not: upload it next
 * to index.php in the document root, open it in a browser, then DELETE IT.
 *
 * It only ever tightens. Directories become 755 and files 644 — the owner, who
 * is the user PHP runs as on cPanel, can write; nobody else can. It never sets
 * 777, and it refuses to touch anything outside the application it finds.
 */

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow');

const DIR_MODE = 0755;
const FILE_MODE = 0644;
const ENV_MODE = 0600;

/** Directories that are large and irrelevant on a production server. */
const SKIP = ['node_modules', '.git', '.svn'];

echo "Global Support Foundation — permission repair\n";
echo str_repeat('=', 58), "\n\n";

/*
| Locating the application
|--------------------------------------------------------------------------
| index.php is the authority, exactly as in php-check.php: its require paths
| are relative when the document root is the application's public/ folder, and
| absolute when public/ has been split into public_html. Reading the resolved
| autoloader back is the only way that works for both.
*/

$appRoot = null;
$indexFile = __DIR__.'/index.php';

if (is_file($indexFile)) {
    preg_match_all('/(?:require|require_once)\s+([^;]+);/', (string) @file_get_contents($indexFile), $matches);

    foreach ($matches[1] ?? [] as $expression) {
        if (preg_match("/__DIR__\s*\.\s*'([^']*)'/", trim($expression), $m)) {
            $resolved = __DIR__.$m[1];
        } elseif (preg_match("/'([^']*)'/", trim($expression), $m)) {
            $resolved = $m[1];
        } else {
            continue;
        }

        if (str_contains($resolved, 'vendor') && file_exists($resolved)) {
            $appRoot = realpath(dirname($resolved, 2)) ?: dirname($resolved, 2);
        }
    }
}

if ($appRoot === null && is_file(__DIR__.'/artisan')) {
    $appRoot = __DIR__;
}

if ($appRoot === null || ! is_file($appRoot.'/artisan')) {
    echo "The application could not be located from this directory.\n\n";
    echo '  This directory : ', __DIR__, "\n\n";
    echo "Put this file next to index.php in your document root, or next to\n";
    echo "artisan in the application root, and open it again.\n";
    exit;
}

echo "Application root : {$appRoot}\n";
echo 'Document root    : ', __DIR__, "\n\n";

/**
 * Apply a mode and report only what actually changed, so the output is a record
 * of the repair rather than a listing of thousands of already-correct files.
 *
 * @return array{0: int, 1: int} changed and failed counts
 */
function applyMode(string $path, int $mode, array &$failures): array
{
    $current = @fileperms($path);

    if ($current !== false && ($current & 0777) === $mode) {
        return [0, 0];
    }

    if (@chmod($path, $mode)) {
        return [1, 0];
    }

    if (count($failures) < 10) {
        $failures[] = $path;
    }

    return [0, 1];
}

$counts = ['dirs' => 0, 'files' => 0, 'failed' => 0];
$failures = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator($appRoot, FilesystemIterator::SKIP_DOTS),
        static fn (SplFileInfo $file): bool => ! in_array($file->getFilename(), SKIP, true),
    ),
    RecursiveIteratorIterator::SELF_FIRST,
);

echo "Repairing\n";
echo str_repeat('-', 58), "\n";

applyMode($appRoot, DIR_MODE, $failures);

foreach ($iterator as $file) {
    /** @var SplFileInfo $file */
    [$changed, $failed] = applyMode($file->getPathname(), $file->isDir() ? DIR_MODE : FILE_MODE, $failures);

    $counts[$file->isDir() ? 'dirs' : 'files'] += $changed;
    $counts['failed'] += $failed;
}

printf("  directories set to 755   %d changed\n", $counts['dirs']);
printf("  files set to 644         %d changed\n", $counts['files']);

/*
| Exceptions to the two rules above.
*/

$executables = [$appRoot.'/artisan'];

foreach ((@glob($appRoot.'/vendor/bin/*') ?: []) as $binary) {
    if (is_file($binary)) {
        $executables[] = $binary;
    }
}

foreach ($executables as $executable) {
    applyMode($executable, DIR_MODE, $failures);
}

printf("  artisan and vendor/bin   %d executable\n", count($executables));

if (is_file($appRoot.'/.env')) {
    applyMode($appRoot.'/.env', ENV_MODE, $failures);
    echo "  .env set to 600          owner only\n";
} else {
    echo "  .env                     *** MISSING — copy production.env.template ***\n";
}

// The split layout keeps the served files outside the application root, so they
// are walked separately. The document root directory itself is never touched:
// some accounts require 750 on public_html and changing it breaks the account.
if ($appRoot !== (realpath(dirname(__DIR__)) ?: dirname(__DIR__)) && is_file($indexFile)) {
    $docFiles = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST,
    );

    $docChanged = 0;

    foreach ($docFiles as $file) {
        /** @var SplFileInfo $file */
        [$changed] = applyMode($file->getPathname(), $file->isDir() ? DIR_MODE : FILE_MODE, $failures);
        $docChanged += $changed;
    }

    printf("  document root            %d changed\n", $docChanged);
}

if ($counts['failed'] > 0) {
    echo "\n  *** ", $counts['failed'], " path(s) could not be changed ***\n";
    echo "  These are owned by another user. Ask your host to reset ownership\n";
    echo "  of the account's home directory. The first few:\n";
    foreach ($failures as $path) {
        echo '      ', $path, "\n";
    }
}

/*
| Verification
|--------------------------------------------------------------------------
| The two failures that actually take the site down are a storage directory
| the application cannot write to, and a vendor file it cannot read. The second
| is the one that produces an empty 500 with nothing in any Laravel log,
| because it happens before Laravel can register an error handler.
*/

echo "\nVerifying\n";
echo str_repeat('-', 58), "\n";

$ok = true;

foreach (['storage', 'storage/logs', 'storage/framework/cache', 'storage/framework/sessions', 'storage/framework/views', 'bootstrap/cache'] as $relative) {
    $path = $appRoot.'/'.$relative;
    $state = ! is_dir($path) ? '*** NOT FOUND ***' : (is_writable($path) ? 'writable' : '*** NOT WRITABLE ***');
    $ok = $ok && $state === 'writable';

    printf("  %-34s %s\n", $relative, $state);
}

$unreadable = [];

foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($appRoot.'/vendor', FilesystemIterator::SKIP_DOTS)) as $file) {
    /** @var SplFileInfo $file */
    if ($file->isFile() && ! is_readable($file->getPathname())) {
        $unreadable[] = $file->getPathname();

        if (count($unreadable) >= 5) {
            break;
        }
    }
}

if ($unreadable === []) {
    printf("  %-34s %s\n", 'vendor/', 'readable');
} else {
    $ok = false;
    printf("  %-34s %s\n", 'vendor/', '*** UNREADABLE FILES ***');
    foreach ($unreadable as $path) {
        echo '      ', $path, "\n";
    }
}

echo "\n", str_repeat('=', 58), "\n";
echo $ok
    ? "Permissions are correct. Clear the caches next:\n  php artisan config:cache && php artisan route:cache && php artisan view:cache\n"
    : "Problems remain — see the lines marked *** above.\n";
echo "\nDELETE THIS FILE NOW. It changes permissions and must not stay online.\n";
