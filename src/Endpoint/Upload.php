<?php

namespace nostriphant\Blossom\Endpoint;

readonly class Upload {
    
    private Upload\Put $put;
    
    public function __construct(string $path) {
        $this->put = new Upload\Put(new \nostriphant\Blossom\Blob\Uncreated($path));

    }
    
    public function __invoke(callable $define) : void {
        $define('OPTIONS', fn(array $attributes, callable $stream) => new Upload\Options()());
        
        $define('PUT', fn(array $attributes, callable $stream) => ($this->put)($stream));
    }
}
