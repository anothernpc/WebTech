<?php

declare(strict_types=1);
const BASE_PATH = __DIR__;
require BASE_PATH . '/Router.php';
$router = new Router();
$router->registerFileManagerRoutes();

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_samesite' => 'Strict',
        'use_strict_mode' => true
    ]);
}

try {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $router->dispatch($requestUri);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
