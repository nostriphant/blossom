<?php

namespace nostriphant\Blossom\Endpoint;

readonly class Blob {
    
    private \Closure $blob_factory;
    
    public function __construct(private string $path) {
        $this->blob_factory = fn(callable $handler) => fn(string $hash) => new \nostriphant\Functional\When(
                                'file_exists', 
                                fn(string $blob_path) => $handler(new \nostriphant\Blossom\Blob($blob_path)), 
                                fn(string $blob_path) => ['status' => 404],
                        )($path . DIRECTORY_SEPARATOR . $hash);
    }
    
    public function __invoke(callable $define) : void {
        $define('OPTIONS', fn(array $attributes) => new Blob\Options()(new \nostriphant\Blossom\Blob($this->path . DIRECTORY_SEPARATOR . $attributes['hash'])));
        $define('GET', fn(array $attributes) => ($this->blob_factory)(new Blob\Get())($attributes['hash']));
    }
}
