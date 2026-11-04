<?php
// Front controller: todo pedido que não é um arquivo real passa por aqui.
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

require ROOT_PATH . '/app/EnvLoader.php';
EnvLoader::load(ROOT_PATH . '/.env');

require ROOT_PATH . '/app/helpers.php';
require ROOT_PATH . '/app/Autoloader.php';

$debug = env('APP_DEBUG') === 'true';
ini_set('display_errors', $debug ? '1' : '0');
error_reporting(E_ALL);

set_exception_handler(function (Throwable $error) use ($debug): void {
    error_log((string) $error);
    if (!headers_sent()) {
        http_response_code(500);
    }
    $message = $debug ? $error->getMessage() : null;
    include ROOT_PATH . '/resources/views/errors/500.php';
});

require ROOT_PATH . '/routes/web.php';
