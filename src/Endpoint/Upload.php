<?php

namespace nostriphant\Blossom\Endpoint;

readonly class Upload {
    
    
    public function __construct(private string $path) {

    }
    
    public function __invoke(callable $define) : void {
        $define(\nostriphant\Blossom\Method::PUT, fn(array $attributes, callable $stream) => new Upload\Put(new \nostriphant\Blossom\Blob\Uncreated($this->path), $stream));
    }
}
