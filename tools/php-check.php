<?php

/**
 * Temporary deployment diagnostic.
 *
 * Upload this to the document root (next to index.php), open it in a browser,
 * then DELETE IT. It reports the PHP version, the extensions, and — most
 * usefully — where the application files actually are relative to the document
 * root, which is the usual cause of a 500 on a first cPanel deployment.
 */

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow');

$required = '8.3.0';
$phpOk = version_compare(PHP_VERSION, $required, '>=');

echo "Global Support Foundation — deployment check\n";
echo str_repeat('=', 58), "\n\n";

echo "PHP version (this process) : ", PHP_VERSION, "\n";
echo "Required                   : >= {$required}\n";
echo "Interface (SAPI)           : ", PHP_SAPI, "\n";
echo "Verdict                    : ", $phpOk ? 'OK' : '*** TOO OLD ***', "\n\n";

if (! $phpOk) {
    echo "The web server is running PHP ", PHP_VERSION, ", but this application\n";
    echo "requires 8.3 or newer. Laravel 13 does not support anything older.\n";
    echo "Set the version in cPanel -> MultiPHP Manager, then reload this page.\n\n";
}

echo "Extensions\n";
echo str_repeat('-', 58), "\n";
$missing = [];
foreach (['pdo_mysql', 'mbstring', 'openssl', 'curl', 'fileinfo', 'gd', 'zip', 'intl', 'tokenizer', 'xml', 'ctype', 'bcmath'] as $extension) {
    if (! extension_loaded($extension)) {
        $missing[] = $extension;
    }
    printf("  %-12s %s\n", $extension, extension_loaded($extension) ? 'ok' : 'MISSING');
}

/*
| Layout
|--------------------------------------------------------------------------
| index.php expects the application one level above the document root. If the
| archive was extracted in the wrong place, or only the public/ folder was
| uploaded, Laravel cannot boot and every URL returns 500.
*/

echo "\nLayout\n";
echo str_repeat('-', 58), "\n";
echo "  This file is in   : ", __DIR__, "\n";
echo "  Parent directory  : ", dirname(__DIR__), "\n\n";

$markers = ['artisan', 'vendor/autoload.php', 'bootstrap/app.php', 'storage', 'composer.json', '.env'];
$appRoot = null;
foreach ([dirname(__DIR__) => 'the parent directory (expected)', __DIR__ => 'this directory'] as $candidate => $label) {
    $found = array_values(array_filter($markers, fn (string $m): bool => file_exists($candidate.DIRECTORY_SEPARATOR.$m)));
    echo "  Application files in {$label}:\n";
    if ($found === []) {
        echo "      none found\n";
    } else {
        echo "      ", implode(', ', $found), "\n";
        if ($appRoot === null && in_array('artisan', $found, true)) {
            $appRoot = $candidate;
        }
    }
}

echo "\n";
if ($appRoot === null) {
    echo "  *** The application was not found. ***\n\n";
    echo "  index.php loads vendor/autoload.php and bootstrap/app.php from one\n";
    echo "  level above this directory. Neither is there, so Laravel cannot start\n";
    echo "  and every page returns 500.\n\n";
    echo "  Extract the archive so the layout is:\n\n";
    echo "      /home/<account>/gsf-website/      <- artisan, vendor, storage, .env\n";
    echo "      /home/<account>/gsf-website/public/   <- document root\n\n";
    echo "  Then point the domain's document root at that public/ folder\n";
    echo "  (cPanel -> Domains). See DEPLOY-CPANEL.md section 3, Option A.\n\n";
    echo "  Contents of the parent directory:\n";
    $entries = @scandir(dirname(__DIR__)) ?: [];
    $entries = array_slice(array_values(array_diff($entries, ['.', '..'])), 0, 30);
    echo $entries === [] ? "      (empty or unreadable)\n" : "      ".implode("\n      ", $entries)."\n";
} else {
    echo "  Application root  : {$appRoot}\n";
    echo "  Layout            : ", $appRoot === dirname(__DIR__) ? "correct" : "*** this file is inside the application root, not public/ ***", "\n\n";

    echo "  Required files\n";
    foreach (['vendor/autoload.php', 'bootstrap/app.php', '.env', 'public/build/manifest.json'] as $file) {
        $path = $appRoot.DIRECTORY_SEPARATOR.$file;
        printf("    %-28s %s\n", $file, file_exists($path) ? 'present' : ($file === '.env' ? 'MISSING - create it from production.env.template' : 'MISSING'));
    }

    echo "\n  Writable paths\n";
    foreach (['storage', 'storage/logs', 'storage/framework', 'storage/framework/views', 'storage/framework/sessions', 'bootstrap/cache'] as $dir) {
        $path = $appRoot.DIRECTORY_SEPARATOR.$dir;
        printf("    %-28s %s\n", $dir, ! is_dir($path) ? 'NOT FOUND' : (is_writable($path) ? 'writable' : '*** NOT WRITABLE ***'));
    }

    $log = $appRoot.'/storage/logs/laravel.log';
    echo "\n  Last error in storage/logs/laravel.log\n";
    if (! is_file($log)) {
        echo "    no log file yet\n";
    } else {
        $lines = @file($log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        $recent = array_slice($lines, -40);
        $errors = array_values(array_filter($recent, fn (string $l): bool => str_contains($l, 'ERROR') || str_contains($l, 'Exception')));
        echo $errors === [] ? "    nothing recent\n" : "    ".substr((string) end($errors), 0, 300)."\n";
    }
}

echo "\n", str_repeat('=', 58), "\n";
if ($missing !== []) {
    echo "Enable in cPanel -> Select PHP Version -> Extensions: ", implode(', ', $missing), "\n";
}
echo "Delete this file when you are finished with it.\n";
