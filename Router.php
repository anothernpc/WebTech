<?php
class Router {
    private $routes = [];

    public function addRoute($action, $handler) {
        $this->routes[$action] = $handler;
    }

    public function dispatch($action) {
        if (!isset($this->routes[$action])) {
            throw new Exception("Route '$action' not defined");
        }

        list($controllerName, $methodName) = explode('@', $this->routes[$action]);

        // Verify controller exists
        $controllerFile = __DIR__."/controllers/$controllerName.php";
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

        return $controller->$methodName();
    }
}