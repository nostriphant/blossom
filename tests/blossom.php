<?php

require_once __DIR__ . '/bootstrap.php';

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $blossom = new \nostriphant\Blossom\Blossom(nostriphant\Blossom\data_directory() . '/files');
    
    $routes = $blossom();
    nostriphant\Functional\Functions::iterator_walk($routes, fn(callable $route) => $route([$r, 'addRoute']));
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}

$routeInfo = $dispatcher->dispatch($httpMethod, rawurldecode($uri));
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        header('HTTP/2 404', true);
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        header('HTTP/2 405', true);
        break;
    case FastRoute\Dispatcher::FOUND:
        $authorization_endpoint = $routeInfo[1];
        
        $handler = $authorization_endpoint($_SERVER['HTTP_AUTHORIZATION'] ?? null);
        
        $response = $handler($routeInfo[2], function() {
            $handle = fopen('php://input', 'rb');
            while (feof($handle) === false) {
                yield fread($handle, 1024);
            }
            fclose($handle);
        });
        
        header('HTTP/2 ' . ($response['status'] ?? '200'), true);
        
        $headers = $response['headers'] ?? [];
        array_walk($headers, fn(string $value, string $header) => header($header.': ' .$value));
        
        if (isset($response['body'])) {
            print $response['body'];
        }
        exit;
}

