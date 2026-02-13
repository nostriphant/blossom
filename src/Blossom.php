<?php

namespace nostriphant\Blossom;

use \nostriphant\Functional\FunctionList;

readonly class Blossom {
    
    public function __construct(private string $path) {
       
    }
    
    static function wrap(string $endpoint, callable $endpoint_factory, array &$endpoint_methods) : callable {
        $endpoint_methods[$endpoint] = [];
        return function(callable $define) use ($endpoint_factory, $endpoint, &$endpoint_methods) {
            return $endpoint_factory(function(Method $method, callable $handler) use ($define, $endpoint, &$endpoint_methods) {
                $endpoint_methods[$endpoint][] = $method;
                return $define($method->name, $endpoint, fn(array $attributes, callable $stream) => $handler($attributes, $stream)());
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
