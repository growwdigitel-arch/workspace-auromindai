<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

if (isset($_GET['__ping'])) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "PHP OK: " . PHP_VERSION . "\n";
    echo "DB_HOST=" . (getenv('DB_HOST') ?: 'unset') . "\n";
    echo "DB_PORT=" . (getenv('DB_PORT') ?: 'unset') . "\n";
    echo "DB_DATABASE=" . (getenv('DB_DATABASE') ?: 'unset') . "\n";
    echo "SESSION_DRIVER=" . (getenv('SESSION_DRIVER') ?: 'unset') . "\n";
    if ($_GET['__ping'] === 'db') {
        $host = getenv('DB_HOST');
        $port = getenv('DB_PORT') ?: '3306';
        $db = getenv('DB_DATABASE');
        $user = getenv('DB_USERNAME');
        $pass = getenv('DB_PASSWORD');
        if ($host && $db && $user) {
            try {
                $t = microtime(true);
                $pdo = new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass, [PDO::ATTR_TIMEOUT => 4]);
                echo "MySQL OK (" . round(microtime(true) - $t, 2) . "s)\n";
            } catch (\Throwable $e) {
                echo "MySQL ERR: " . $e->getMessage() . "\n";
            }
        }
    }
    exit(0);
}

if (!file_exists(__DIR__ . '/../.env') && !getenv('APP_KEY') && !isset($_ENV['APP_KEY']) && !getenv('VERCEL')) {
    $GLOBALS["error_type"] = "env-missing";
    include('error_install.php');
    exit(1);
}

if (version_compare(PHP_VERSION, '8.2.0') < 0){
    $GLOBALS["error_type"] = "php-version";
    include('error_install.php');
    exit(1);
}

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is maintenance / demo mode via the "down" command we
| will require this file so that any prerendered template can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists(__DIR__.'/../storage/framework/maintenance.php')) {
    require __DIR__.'/../storage/framework/maintenance.php';
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = tap($kernel->handle(
    $request = Request::capture()
))->send();

$kernel->terminate($request, $response);
