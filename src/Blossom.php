<?php

namespace nostriphant\Blossom;

use \nostriphant\Functional\FunctionList;

readonly class Blossom {
    
    public function __construct(private string $path) {
       
    }
    
    static function wrap(string $endpoint, Endpoint\Factory $endpoint_factory, array &$endpoint_methods) : callable {
        $endpoint_methods[$endpoint] = [];
        return function(callable $define) use ($endpoint_factory, $endpoint, &$endpoint_methods) {
            return $endpoint_factory(function(Method $method, callable $handler) use ($define, $endpoint, $endpoint_factory, &$endpoint_methods) {
                $endpoint_methods[$endpoint][] = $method;
                return $define($method->name, $endpoint, function(array $attributes, callable $stream) use ($endpoint_factory, $handler) {
                    $response = $handler(...$endpoint_factory->attributes($attributes, $stream))();
                    
                    $additional_headers = ['Access-Control-Allow-Origin' => '*'];
                    if (isset($response['body']) === false) {
                    } elseif(isset($headers['Content-Length']) === false) {
                        $additional_headers['Content-Length'] = strlen($response['body']);
                    }
                    
                    $response['headers'] = array_merge($additional_headers, $response['headers'] ?? []);
                    
                    return $response;
                });
            });
        };
    }

    public function __invoke() : \Generator {
        $endpoint_methods = [];
        yield self::wrap('/upload', new Endpoint\Upload($this->path), $endpoint_methods);
        yield self::wrap('/{hash:\w+}[.{ext:\w+}]', new Endpoint\Blob($this->path), $endpoint_methods);
        foreach ($endpoint_methods as $endpoint => $methods) {
            if (in_array(Method::GET, $methods) && in_array(Method::HEAD, $methods) === false) {
                $methods[] = Method::HEAD;
            }
            yield fn(callable $define) => $define('OPTIONS', $endpoint, new Endpoint\Options(...$methods));
        }
    }
}
