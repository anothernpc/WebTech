<?php
declare(strict_types=1);
const BASE_PATH = __DIR__;
require BASE_PATH . '/Router.php';
$router = new Router();
$router->registerFileManagerRoutes();
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
