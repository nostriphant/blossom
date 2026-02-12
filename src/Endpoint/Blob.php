<?php

namespace nostriphant\Blossom\Endpoint;

readonly class Blob {
    
    private \Closure $blob_factory;
    
    public function __construct(string $path) {
        $this->blob_factory = fn(callable $handler) => fn(string $hash) => new \nostriphant\Functional\When(
                                'file_exists', 
                                fn(string $blob_path) => $handler(new \nostriphant\Blossom\Blob($blob_path)), 
                                fn(string $blob_path) => ['status' => 404],
                        )($path . DIRECTORY_SEPARATOR . $hash);
    }
    
    public function __invoke(callable $define) : void {
        $redefine = fn(string $method, callable $handler) => $define($method, fn(array $attributes) => $handler($attributes['hash']));
        $redefine('OPTIONS', ($this->blob_factory)(new Blob\Options()));
        $redefine('GET', ($this->blob_factory)(new Blob\Get()));
    }
}
