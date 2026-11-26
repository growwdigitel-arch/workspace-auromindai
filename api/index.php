<?php

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
