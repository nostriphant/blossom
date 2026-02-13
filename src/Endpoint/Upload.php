<?php

namespace nostriphant\Blossom\Endpoint;

readonly class Upload {
    
    
    public function __construct(private string $path) {

    }
    
    public function __invoke(callable $define) : void {
        $define('OPTIONS', fn() => new Options(\nostriphant\Blossom\Method::PUT));
        $define('PUT', fn(array $attributes, callable $stream) => new Upload\Put(new \nostriphant\Blossom\Blob\Uncreated($this->path), $stream));
    }
}
