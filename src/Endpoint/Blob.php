<?php

namespace nostriphant\Blossom\Endpoint;

readonly class Blob {
    
    
    public function __construct(private string $path) {
    }
    
    public function __invoke(callable $define) : void {
        $define('OPTIONS', fn() => new Options(\nostriphant\Blossom\Method::GET, \nostriphant\Blossom\Method::HEAD));
        $define('GET', fn(array $attributes) => new Blob\Get(new \nostriphant\Blossom\Blob($this->path . DIRECTORY_SEPARATOR . $attributes['hash'])));
    }
}
