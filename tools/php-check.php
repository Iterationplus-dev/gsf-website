<?php

/**
 * Temporary deployment diagnostic.
 *
 * Upload this next to index.php in the document root, open it in a browser,
 * then DELETE IT. It reports the PHP version, the extensions, where the
 * application actually is, whether it can boot, and the last error it logged.
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
    echo "Set it in cPanel -> MultiPHP Manager, then reload this page.\n\n";
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
| Where the application is
|--------------------------------------------------------------------------
| index.php is the authority. It works for both supported layouts: the paths
| are relative when the document root is public/, and absolute when public/
| has been split into public_html. Reading them back is the only reliable
| check, so everything below keys off whatever they resolve to.
*/

echo "\nApplication\n";
echo str_repeat('-', 58), "\n";
echo "  Document root     : ", __DIR__, "\n";

$indexFile = __DIR__.'/index.php';
$appRoot = null;
$bootFailure = null;

if (! is_file($indexFile)) {
    $bootFailure = "There is no index.php in this directory, so nothing can be served.\n".
                   "  Copy the contents of the application's public/ folder here, or\n".
                   "  point the document root at that folder instead.";
} else {
    $source = (string) @file_get_contents($indexFile);
    preg_match_all('/(?:require|require_once)\s+([^;]+);/', $source, $matches);

    echo "\n  Paths index.php loads\n";
    foreach ($matches[1] ?? [] as $expression) {
        $expression = trim($expression);

        if (preg_match("/__DIR__\s*\.\s*'([^']*)'/", $expression, $m)) {
            $resolved = __DIR__.$m[1];
        } elseif (preg_match("/'([^']*)'/", $expression, $m)) {
            $resolved = $m[1];
        } else {
            continue;
        }

        $exists = file_exists($resolved);
        $shown = strlen($resolved) > 44 ? '...'.substr($resolved, -41) : $resolved;
        printf("    %-44s %s\n", $shown, $exists ? 'found' : '*** NOT FOUND ***');

        if ($exists && str_contains($resolved, 'vendor')) {
            $appRoot = realpath(dirname($resolved, 2)) ?: dirname($resolved, 2);
        } elseif (! $exists && ! str_contains($resolved, 'maintenance')) {
            $bootFailure = "index.php points at files that are not there. In the split\n".
                           "  layout every __DIR__.'/../' in index.php must be changed —\n".
                           "  there are THREE (maintenance check, autoloader, bootstrap).";
        }
    }
}

echo "\n";

if ($appRoot === null) {
    echo "  *** The application could not be located. ***\n\n";
    if ($bootFailure !== null) {
        echo "  ", $bootFailure, "\n\n";
    }

    $home = dirname(__DIR__);
    $candidates = [];
    foreach ((@scandir($home) ?: []) as $entry) {
        if ($entry === '.' || $entry === '..' || str_starts_with($entry, '.')) {
            continue;
        }
        $path = $home.DIRECTORY_SEPARATOR.$entry;
        if (is_dir($path) && is_file($path.'/artisan')) {
            $candidates[] = $path;
        }
    }

    if ($candidates !== []) {
        echo "  Found an application at:\n";
        foreach ($candidates as $path) {
            echo "      {$path}\n";
        }
        echo "\n  Point the document root at that folder's public/ directory\n";
        echo "  (cPanel -> Domains), or edit index.php here to load from it.\n";
    }

    echo "\n", str_repeat('=', 58), "\n";
    echo "Delete this file when you are finished with it.\n";
    exit;
}

echo "  Application root  : {$appRoot}\n";
echo "  Layout            : ", $appRoot === (realpath(dirname(__DIR__)) ?: dirname(__DIR__))
    ? 'document root is the application public/ folder'
    : 'public/ split into the document root', "
";

if ($bootFailure !== null) {
    echo "\n  *** ", $bootFailure, "\n";
}

/*
| Everything the application needs before it can serve a request.
*/

echo "\n  Required files\n";
$envPath = $appRoot.'/.env';
$hasEnv = is_file($envPath);
foreach (['vendor/autoload.php' => 'vendor/autoload.php', 'bootstrap/app.php' => 'bootstrap/app.php', '.env' => '.env', 'public/build/manifest.json' => 'public/build/manifest.json'] as $relative => $label) {
    $exists = file_exists($appRoot.DIRECTORY_SEPARATOR.$relative);
    printf("    %-30s %s\n", $label, $exists ? 'present' : ($relative === '.env' ? '*** MISSING — copy production.env.template ***' : '*** MISSING ***'));
}

if ($hasEnv) {
    $env = (string) @file_get_contents($envPath);
    preg_match('/^APP_KEY\s*=\s*(.*)$/m', $env, $keyMatch);
    $appKey = trim($keyMatch[1] ?? '', " \"'");
    preg_match('/^APP_DEBUG\s*=\s*(.*)$/m', $env, $debugMatch);
    preg_match('/^APP_URL\s*=\s*(.*)$/m', $env, $urlMatch);
    preg_match('/^DB_DATABASE\s*=\s*(.*)$/m', $env, $dbMatch);

    echo "\n  Environment\n";
    printf("    %-30s %s\n", 'APP_KEY', $appKey === '' ? '*** EMPTY — run php artisan key:generate ***' : 'set');
    printf("    %-30s %s\n", 'APP_DEBUG', trim($debugMatch[1] ?? '', " \"'") ?: '(unset)');
    printf("    %-30s %s\n", 'APP_URL', trim($urlMatch[1] ?? '', " \"'") ?: '(unset)');
    printf("    %-30s %s\n", 'DB_DATABASE', trim($dbMatch[1] ?? '', " \"'") === '' ? '*** EMPTY ***' : 'set');
}

echo "\n  Writable paths\n";
foreach (['storage', 'storage/logs', 'storage/framework/views', 'storage/framework/sessions', 'storage/framework/cache', 'bootstrap/cache'] as $dir) {
    $path = $appRoot.DIRECTORY_SEPARATOR.$dir;
    printf("    %-30s %s\n", $dir, ! is_dir($path) ? '*** NOT FOUND ***' : (is_writable($path) ? 'writable' : '*** NOT WRITABLE ***'));
}

$log = $appRoot.'/storage/logs/laravel.log';
echo "\n  Most recent error in storage/logs/laravel.log\n";
if (! is_file($log)) {
    echo "    no log file yet — nothing has been written\n";
} else {
    $lines = @file($log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    $errors = array_values(array_filter(array_slice($lines, -300), fn (string $l): bool => str_contains($l, '.ERROR')));
    if ($errors === []) {
        echo "    nothing recent\n";
    } else {
        foreach (array_slice($errors, -2) as $line) {
            echo "    ", substr($line, 0, 240), "\n";
        }
    }
}

/*
| The server's own error log
|--------------------------------------------------------------------------
| A fatal raised before Laravel registers its handler never reaches
| storage/logs/laravel.log. Apache writes it here instead, which is why an
| empty 500 with no Laravel log usually has its explanation in this file.
*/

echo "\n  Server error log\n";
$serverLogs = array_values(array_filter([
    __DIR__.'/error_log',
    $appRoot.'/error_log',
    dirname(__DIR__).'/error_log',
    dirname(__DIR__).'/logs/error_log',
], 'is_file'));

if ($serverLogs === []) {
    echo "    none found next to index.php or in the home directory\n";
} else {
    foreach (array_slice($serverLogs, 0, 2) as $serverLog) {
        echo "    ", $serverLog, "\n";
        $entries = @file($serverLog, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach (array_slice($entries, -6) as $entry) {
            echo "      ", substr($entry, 0, 220), "\n";
        }
    }
}

/*
| Boot attempt
|--------------------------------------------------------------------------
| Everything above can pass while the application still cannot start. Loading
| the autoloader and the bootstrap file is the only way to see the real fatal.
| The shutdown handler reports it, because a fatal stops this script too.
*/

echo "\n  Boot attempt\n";

register_shutdown_function(static function () use ($missing): void {
    $error = error_get_last();

    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        echo "    *** FATAL - this is why the site returns 500 ***\n";
        echo "    ", $error['message'], "\n";
        echo "    in ", $error['file'], " line ", $error['line'], "\n";
    }

    if ($missing !== []) {
        echo "\n  Enable in cPanel -> Select PHP Version -> Extensions: ", implode(', ', $missing), "\n";
    }

    echo "\n", str_repeat('=', 58), "\n";
    echo "Delete this file when you are finished with it.\n";
});

try {
    require $appRoot.'/vendor/autoload.php';
    echo "    autoloader           loaded\n";

    $app = require $appRoot.'/bootstrap/app.php';
    echo "    bootstrap/app.php    loaded\n";

    $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "    HTTP kernel          resolved\n";
    echo "\n    The application boots. If the site still returns 500 the failure\n";
    echo "    is in handling the request - see the Laravel log above.\n";
} catch (Throwable $e) {
    echo "    *** ", get_class($e), " ***\n";
    echo "    ", $e->getMessage(), "\n";
    echo "    in ", $e->getFile(), " line ", $e->getLine(), "\n";
}
