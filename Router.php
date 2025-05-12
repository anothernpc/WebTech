<?php
declare(strict_types=1);
class Router {
    private array $routes = [];

    public function add(string $path, string $controllerMethod): void
    {
        $this->routes[$path] = $controllerMethod;
    }

    public function registerFileManagerRoutes(): void
    {
        $this->add('/WebTech/admin/file/list', 'AdminController/listFiles');
        $this->add('/WebTech/admin/file/upload', 'AdminController/uploadFiles');
        $this->add('/WebTech/admin/file/download', 'AdminController/downloadFile');
        $this->add('/WebTech/admin/file/getContent', 'AdminController/getFileContent');
        $this->add('/WebTech/admin/file/saveContent', 'AdminController/saveFileContent');
        $this->add('/WebTech/admin/file/preview', 'AdminController/previewFile');
        $this->add('/WebTech/admin/file/delete', 'AdminController/deleteFile');

        $this->add('/WebTech/events', 'EventsController/listEvents');
        $this->add('/WebTech/events/view', 'EventsController/showEvent');
        $this->add('/WebTech/events/add-to-cart', 'EventsController/addToCart');
        $this->add('/WebTech/cart', 'CartController/viewCart');
        $this->add('/WebTech/cart/remove', 'CartController/removeFromCart');
        $this->add('/WebTech/cart/count', 'CartController/getCartCount');
        $this->add('/WebTech/cart/data', 'CartController/getCartData');
    }

    public function dispatch(string $requestUri): void
    {
        try {
            $path = parse_url($requestUri, PHP_URL_PATH) ?? '/';

            $baseDirectory = '/var/www/WebTech';
            if (strpos($path, $baseDirectory) === 0) {
                $path = substr($path, strlen($baseDirectory));
            }

            $query = parse_url($requestUri, PHP_URL_QUERY);

            $params = [];
            if ($query !== null) {
                parse_str($query, $params);
            }

            if (isset($this->routes[$path])) {
                $this->callControllerMethod($this->routes[$path], $params);
                return;
            }

            throw new Exception("Route not found: $path");

        } catch (Exception $e) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }


    protected function callControllerMethod(string $controllerMethod, array $params = []): void
    {
        list($controllerName, $methodName) = explode('/', $controllerMethod);

        $controllerFile = __DIR__ . "/controllers/$controllerName.php";
        if (!file_exists($controllerFile)) {
            throw new Exception("Controller file not found: $controllerName.php");
        }

        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            throw new Exception("Class $controllerName not found");
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $methodName)) {
            throw new Exception("Method $methodName not found in $controllerName");
        }
        $response = $controller->$methodName($params);
        if (!is_null($response)) {
            if (!isset($response['status'])) {
                $response['status'] = 'success';
            }
            echo json_encode($response);
        }
    }

}
