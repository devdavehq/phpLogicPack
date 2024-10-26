<?php
class Router
{
    private static $routes = [];
    private static $routeCounter = 0;

    public static function addRoute($method, $url, $middleware)
    {
        $routeId = 'route_' . self::$routeCounter++;
        self::$routes[$routeId] = [
            'method' => strtoupper($method),
            'url' => $url,
            'middleware' => $middleware
        ];
        return $routeId;
    }

    public static function handleRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = $_SERVER['REQUEST_URI'];

        $matchedPath = false;

        foreach (self::$routes as $route) {
            $matches = self::match($path, $route['url']);
            if ($matches !== false) {
                $matchedPath = true;
                if ($route['method'] === $method) {
                    $queryParams = [];
                    $queryString = parse_url($path, PHP_URL_QUERY);
                    if ($queryString !== null) {
                        parse_str($queryString, $queryParams);
                    }
                    $allParams = array_merge($matches, $queryParams);
                    
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
                    
                    return $route['middleware']($allParams, $matches, $requestData);
                }
            }
        }

        if ($matchedPath) {
            self::sendResponse(405, 'Method Not Allowed');
        } else {
            self::sendResponse(404, 'Path Not Found');
        }
    }

    private static function match($path, $url)
    {
        $path = parse_url($path, PHP_URL_PATH);
        $urlParts = parse_url($url);
        $urlPath = $urlParts['path'];

        // Convert the URL path into a regex pattern for matching named parameters
        $urlPattern = preg_replace_callback('/\{(\w+)\}/', function ($matches) {
            return '(?P<' . $matches[1] . '>[^/]+)';
        }, $urlPath);

        // Escape forward slashes and add start/end anchors
        $urlPattern = '/^' . str_replace('/', '\/', $urlPattern) . '$/';

        if (preg_match($urlPattern, $path, $matches)) {
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            // If there's a query string in the URL, add it to the params
            if (isset($urlParts['query'])) {
                parse_str($urlParts['query'], $queryParams);
                $params = array_merge($params, $queryParams);
            }

            return $params;
        }

        return false;
    }

    public static function post($url, $middleware)
    {
        return self::addRoute('POST', $url, $middleware);
    }

    public static function get($url, $middleware)
    {
        return self::addRoute('GET', $url, $middleware);
    }

    public static function put($url, $middleware)
    {
        return self::addRoute('PUT', $url, $middleware);
    }

    public static function delete($url, $middleware)
    {
        return self::addRoute('DELETE', $url, $middleware);
    }

    private static function sendResponse($statusCode, $message)
    {
        //http_response_code($statusCode);
        echo json_encode(['status' => $statusCode, 'message' => $message]);
        exit;
    }
}
