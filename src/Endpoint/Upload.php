<?php

namespace nostriphant\Blossom\Endpoint;

readonly class Upload {
    
    public function __construct(private string $path) {

    }
    
    public function __invoke(callable $define) : void {
        $redefine = fn(string $method, callable $handler) => $define($method, '/upload', fn(array $attributes, callable $stream) => $handler(new \nostriphant\Blossom\Blob\Uncreated($this->path), $stream));
        $redefine('OPTIONS', new Upload\Options());
        $redefine('PUT', new Upload\Put());
    }
}
