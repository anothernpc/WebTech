<?php
class Router {
    private $routes = [];

    public function add(string $path, string $controllerMethod): void
    {
        $this->routes[$path] = $controllerMethod;
    }

    public function registerFileManagerRoutes(): void
    {
        $this->add('/admin/file/list', 'AdminController/listFiles');
        $this->add('/admin/file/upload', 'AdminController/uploadFiles');
        $this->add('/admin/file/download', 'AdminController/downloadFile');
        $this->add('/admin/file/getContent', 'AdminController/getFileContent');
        $this->add('/admin/file/saveContent', 'AdminController/saveFileContent');
        $this->add('/admin/file/preview', 'AdminController/previewFile');
        $this->add('/admin/file/delete', 'AdminController/deleteFile');
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
