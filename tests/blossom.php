<?php

require_once __DIR__ . '/bootstrap.php';

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $blob_factory = new \nostriphant\Blossom\Blob\Factory(nostriphant\Blossom\data_directory() . '/files', fn() => ['status' => 404]);
    $blossom = new \nostriphant\Blossom\Blossom($blob_factory);
    $routes = $blossom(new \nostriphant\Functional\FunctionList());
    $routes(fn(string $method, string $path, callable $endpoint) => $r->addRoute($method, $path, $endpoint));
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
        $handler = $routeInfo[1];
        $response = $handler($routeInfo[2]);
        
        header('HTTP/2 ' . ($response['status'] ?? '200'), true);
        
        $headers = $response['headers'] ?? [];
        array_walk($headers, fn(string $value, string $header) => header($header.': ' .$value));

        if (isset($response['body'])) {
            print $response['body'];
        }
        exit;
}

