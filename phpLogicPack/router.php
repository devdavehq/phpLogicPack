<?php
class Router
{
    private static $routes = [];
    private static $routeCounter = 0;
    private static $groupStack = [];
    private static $namedRoutes = [];
    private static $fallbackHandler;
    private static $middlewareGroups = [];

    public static function addRoute($method, $url, $handler, $middleware = null)
    {
        $groupAttributes = self::getGroupAttributes();
        $url = $groupAttributes['prefix'] . $url;
        $middleware = array_merge($groupAttributes['middleware'], self::resolveMiddleware($middleware));

        $routeId = 'route_' . self::$routeCounter++;
        self::$routes[$routeId] = [
            'method' => strtoupper($method),
            'url' => $url,
            'handler' => $handler,
            'middleware' => $middleware
        ];
        return new Route($routeId);
    }

    public static function handleRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = $_SERVER['REQUEST_URI'];

        foreach (self::$routes as $route) {
            $matches = self::match($path, $route['url']);
            if ($matches !== false && $route['method'] === $method) {
                $queryParams = [];
                $queryString = parse_url($path, PHP_URL_QUERY);
                if ($queryString !== null) {
                    parse_str($queryString, $queryParams);
                }
                $allParams = array_merge($matches, $queryParams);

                // Execute middleware if it exists
                foreach ($route['middleware'] as $middleware) {
                    $middlewareResult = $middleware($allParams, $matches);
                    if ($middlewareResult === false) {
                        // If middleware returns false, stop execution
                        return;
                    }
                }

                // Execute the main handler
                switch ($method) {
                    case 'POST':
                        $requestData = $_POST;
                        break;
                    case 'GET':
                        $requestData = $_GET;
                        break;
                    case 'PUT':
                    case 'DELETE':
                        $input = file_get_contents("php://input");
                        if (!empty($input)) {
                            parse_str($input, $requestData);
                        } else {
                            $requestData = [];
                        }
                        break;
                    default:
                        $requestData = [];
                }

                $response = $route['handler']($allParams, $matches, $requestData);
                self::sendResponse(200, $response);
                return;
            }
        }

        if (self::$fallbackHandler) {
            $response = call_user_func(self::$fallbackHandler);
            self::sendResponse(404, $response);
        } else {
            self::sendResponse(404, 'Not Found');
        }
    }

    private static function match($path, $url)
    {
        $path = parse_url($path, PHP_URL_PATH);
        $urlParts = parse_url($url);
        $urlPath = $urlParts['path'];

        $urlPattern = preg_replace_callback('/\{(\w+)\}/', function ($matches) {
            return '(?P<' . $matches[1] . '>[^/]+)';
        }, $urlPath);

        $urlPattern = '/^' . str_replace('/', '\/', $urlPattern) . '$/';

        if (preg_match($urlPattern, $path, $matches)) {
            return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        }

        return false;
    }

    public static function get($url, $handler, $middleware = null)
    {
        return self::addRoute('GET', $url, $handler, $middleware);
    }

    public static function post($url, $handler, $middleware = null)
    {
        return self::addRoute('POST', $url, $handler, $middleware);
    }

    public static function put($url, $handler, $middleware = null)
    {
        return self::addRoute('PUT', $url, $handler, $middleware);
    }

    public static function delete($url, $handler, $middleware = null)
    {
        return self::addRoute('DELETE', $url, $handler, $middleware);
    }

    private static function sendResponse($statusCode, $message)
    {
        http_response_code($statusCode);
        echo json_encode(['status' => $statusCode, 'message' => $message]);
        exit;
    }

    public static function group($attributes, $callback)
    {
        self::$groupStack[] = $attributes;
        call_user_func($callback);
        array_pop(self::$groupStack);
    }

    private static function getGroupAttributes()
    {
        $prefix = '';
        $middleware = [];
        foreach (self::$groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
            if (isset($group['middleware'])) {
                $middleware = array_merge($middleware, (array)$group['middleware']);
            }
        }
        return ['prefix' => $prefix, 'middleware' => $middleware];
    }

    public static function url($name, $parameters = [])
    {
        if (!isset(self::$namedRoutes[$name])) {
            throw new Exception("Route not found: $name");
        }
        $url = self::$routes[self::$namedRoutes[$name]]['url'];
        foreach ($parameters as $key => $value) {
            $url = str_replace("{{$key}}", $value, $url);
        }
        return $url;
    }

    public static function fallback($handler)
    {
        self::$fallbackHandler = $handler;
    }

    public static function middlewareGroup($name, array $middleware)
    {
        self::$middlewareGroups[$name] = $middleware;
    }

    private static function resolveMiddleware($middleware)
    {
        if (is_string($middleware) && isset(self::$middlewareGroups[$middleware])) {
            return self::$middlewareGroups[$middleware];
        }
        return (array)$middleware;
    }
}

include_once 'namerout.php';
