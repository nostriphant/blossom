<?php

namespace nostriphant\Blossom\Endpoint;

readonly class Blob {
    
    
    public function __construct(private string $path) {
    }
    
    public function __invoke(callable $define) : void {
        $define(\nostriphant\Blossom\Method::GET, fn(array $attributes) => new Blob\Get(new \nostriphant\Blossom\Blob($this->path . DIRECTORY_SEPARATOR . $attributes['hash'])));
    }
}
