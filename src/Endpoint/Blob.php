<?php

namespace nostriphant\Blossom\Endpoint;

readonly class Blob {
    
    
    public function __construct(private string $path) {
    }
    
    public function __invoke(callable $define) : void {
        $define('OPTIONS', fn(array $attributes) => new Blob\Options()());
        $define('GET', fn(array $attributes) => new Blob\Get(new \nostriphant\Blossom\Blob($this->path . DIRECTORY_SEPARATOR . $attributes['hash']))());
    }
}
