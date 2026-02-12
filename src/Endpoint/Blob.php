<?php

namespace nostriphant\Blossom\Endpoint;

readonly class Blob {
    
    
    public function __construct(private string $path) {
    }
    
    public function __invoke(callable $define) : void {
        $define('OPTIONS', fn(array $attributes) => new Blob\Options()());
        $define('GET', fn(array $attributes) => (new \nostriphant\Functional\When(
                                'file_exists', 
                                fn(string $blob_path) => (new Blob\Get(new \nostriphant\Blossom\Blob($blob_path)))(), 
                                fn(string $blob_path) => ['status' => 404],
                        ))($this->path . DIRECTORY_SEPARATOR . $attributes['hash']));
    }
}
