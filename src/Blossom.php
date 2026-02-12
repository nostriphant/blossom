<?php

namespace nostriphant\Blossom;

use \nostriphant\Functional\FunctionList;

readonly class Blossom {
    
    public function __construct(private string $path) {
       
    }
    
    static function wrap(string $endpoint, callable $endpoint_factory) : callable {
        return fn(callable $define) => $endpoint_factory(fn(string $method, callable $handler) => $define($method, $endpoint, $handler));
    }

    public function __invoke() : \Generator {
        yield self::wrap('/upload', new Endpoint\Upload($this->path));
        yield self::wrap('/{hash:\w+}[.{ext:\w+}]', new Endpoint\Blob($this->path));
    }
}
