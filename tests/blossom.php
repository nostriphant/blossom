<?php

require_once __DIR__ . '/bootstrap.php';

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $blossom = \nostriphant\Blossom\Blossom::fromPath(nostriphant\Blossom\data_directory() . '/files');
    
    nostriphant\Functional\Functions::iterator_walk($blossom, fn(callable $route) => $route([$r, 'addRoute']));
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
        $headers = array_filter($_SERVER, fn(string $key) => str_starts_with($key, 'HTTP_'), ARRAY_FILTER_USE_KEY);
        $response = $routeInfo[1](new \nostriphant\Blossom\HTTP\ServerRequest($headers, $routeInfo[2], fopen('php://input', 'rb')));
        
        header('HTTP/2 ' . ($response['status'] ?? '200'), true);
        
        $headers = $response['headers'] ?? [];
        array_walk($headers, fn(string $value, string $header) => header($header.': ' .$value));
        
        if (isset($response['body'])) {
            print $response['body'];
        }
        exit;
}

