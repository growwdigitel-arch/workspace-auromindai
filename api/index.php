<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/plain; charset=utf-8');
        }
        echo "PHP Fatal Error: {$error['message']}\nFile: {$error['file']}:{$error['line']}\n";
    }
});

if (isset($_GET['__diag'])) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "PHP Version: " . PHP_VERSION . "\n";
    echo "VERCEL env: " . (getenv('VERCEL') ?: 'unset') . "\n";
    echo "APP_KEY set: " . (getenv('APP_KEY') ? 'yes' : 'no') . "\n";
    echo "DB_HOST: " . (getenv('DB_HOST') ?: 'unset') . "\n";
    echo "DB_PORT: " . (getenv('DB_PORT') ?: 'unset') . "\n";
    echo "DB_DATABASE: " . (getenv('DB_DATABASE') ?: 'unset') . "\n";
    echo "vendor/autoload exists: " . (file_exists(__DIR__ . '/../vendor/autoload.php') ? 'yes' : 'no') . "\n";
    $host = getenv('DB_HOST');
    $port = getenv('DB_PORT') ?: '3306';
    $db = getenv('DB_DATABASE');
    $user = getenv('DB_USERNAME');
    $pass = getenv('DB_PASSWORD');
    if ($host && $db && $user) {
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$db}";
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_TIMEOUT => 3]);
            echo "MySQL connection: OK\n";
        } catch (\Throwable $e) {
            echo "MySQL connection FAILED: " . $e->getMessage() . "\n";
        }
    }
    exit(0);
}

// Ensure writable /tmp directories exist on Vercel serverless read-only filesystem
if (getenv('VERCEL') || isset($_ENV['VERCEL']) || !is_writable(__DIR__ . '/../storage')) {
    $tmpDirs = [
        '/tmp/storage/framework/views',
        '/tmp/storage/framework/cache/data',
        '/tmp/storage/framework/sessions',
        '/tmp/storage/logs',
        '/tmp/storage/app/public',
        '/tmp/bootstrap/cache',
    ];
    foreach ($tmpDirs as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
    }

    $envDefaults = [
        'VIEW_COMPILED_PATH' => '/tmp/storage/framework/views',
        'APP_PACKAGES_CACHE' => '/tmp/bootstrap/cache/packages.php',
        'APP_SERVICES_CACHE' => '/tmp/bootstrap/cache/services.php',
        'APP_ROUTES_CACHE' => '/tmp/bootstrap/cache/routes-v7.php',
        'APP_EVENTS_CACHE' => '/tmp/bootstrap/cache/events.php',
        'APP_CONFIG_CACHE' => '/tmp/bootstrap/cache/config.php',
        'LOG_CHANNEL' => 'stderr',
    ];

    foreach ($envDefaults as $key => $val) {
        if (!getenv($key) && !isset($_ENV[$key])) {
            putenv("$key=$val");
            $_ENV[$key] = $val;
            $_SERVER[$key] = $val;
        }
    }
}

try {
    // Forward Vercel serverless requests to Laravel's public/index.php
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    error_log('Vercel PHP Fatal Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString());
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo "Application Error: " . $e->getMessage() . "\nFile: " . $e->getFile() . ":" . $e->getLine();
}
