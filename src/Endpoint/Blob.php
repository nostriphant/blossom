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
        $wrap = fn(callable $handler) => fn(array $attributes) => ($this->blob_factory)($handler)($attributes['hash']);
        $define('OPTIONS', $wrap(new Blob\Options()));
        $define('GET', $wrap(new Blob\Get()));
    }
}
