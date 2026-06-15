<?php
/**
 * Tiv Culture Archive - Router
 * Handles URL routing and dispatching to controllers
 */

class Router
{
    private array $routes = [];
    private array $params = [];

    /**
     * Add a route to the routing table
     */
    public function add(string $method, string $route, string $controller, string $action): self
    {
        // Convert route to regex pattern
        $route = preg_replace('/\//', '\\/', $route);
        $route = preg_replace('/\{([a-zA-Z][a-zA-Z0-9_]*)\}/', '(?P<\1>[a-zA-Z0-9_-]+)', $route);
        $route = preg_replace('/\{([a-zA-Z][a-zA-Z0-9_]*):([^\}]+)\}/', '(?P<\1>\2)', $route);
        $route = '/^' . $route . '$/i';

        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $route,
            'controller' => $controller,
            'action' => $action
        ];

        return $this;
    }

    /**
     * Add GET route
     */
    public function get(string $route, string $controller, string $action): self
    {
        return $this->add('GET', $route, $controller, $action);
    }

    /**
     * Add POST route
     */
    public function post(string $route, string $controller, string $action): self
    {
        return $this->add('POST', $route, $controller, $action);
    }

    /**
     * Match the URL to routes
     */
    public function match(string $url, string $method): bool
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method && $route['method'] !== 'ANY') {
                continue;
            }

            if (preg_match($route['pattern'], $url, $matches)) {
                $this->params = [
                    'controller' => $route['controller'],
                    'action' => $route['action']
                ];

                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $this->params[$key] = $value;
                    }
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Get route parameters
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Dispatch the route
     */
    public function dispatch(string $url): void
    {
        $url = $this->removeQueryString($url);
        $method = $_SERVER['REQUEST_METHOD'];

        if ($this->match($url, $method)) {
            $controller = $this->params['controller'];
            $action = $this->params['action'];

            $controllerFile = BASE_PATH . '/controllers/' . $controller . '.php';

            if (file_exists($controllerFile)) {
                require_once $controllerFile;

                if (class_exists($controller)) {
                    $controllerInstance = new $controller();

                    if (method_exists($controllerInstance, $action)) {
                        // Remove controller and action from params
                        unset($this->params['controller'], $this->params['action']);

                        // Call the action with remaining params
                        call_user_func_array([$controllerInstance, $action], $this->params);
                    } else {
                        $this->handleError(404, "Method {$action} not found in {$controller}");
                    }
                } else {
                    $this->handleError(404, "Controller class {$controller} not found");
                }
            } else {
                $this->handleError(404, "Controller file {$controller}.php not found");
            }
        } else {
            $this->handleError(404);
        }
    }

    /**
     * Remove query string from URL
     */
    private function removeQueryString(string $url): string
    {
        if ($url !== '') {
            $parts = explode('&', $url, 2);
            if (strpos($parts[0], '=') === false) {
                $url = $parts[0];
            } else {
                $url = '';
            }
        }
        return $url;
    }

    /**
     * Handle HTTP errors
     */
    public function handleError(int $code, string $message = ''): void
    {
        http_response_code($code);

        $errorFile = BASE_PATH . '/views/errors/' . $code . '.php';

        if (file_exists($errorFile)) {
            $view = new View();
            $view->render('errors/' . $code, [
                'code' => $code,
                'message' => $message
            ]);
        } else {
            echo "<h1>Error {$code}</h1>";
            if (ENVIRONMENT === 'development' && $message) {
                echo "<p>{$message}</p>";
            }
        }
        exit;
    }
}
