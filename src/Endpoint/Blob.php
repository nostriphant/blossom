<?php

namespace nostriphant\Blossom\Endpoint;

use nostriphant\Blossom\Endpoint;

readonly class Blob implements Endpoint {
    
    private \Closure $blob_factory;
    
    public function __construct(string $path) {
        $blob_existing = fn(callable $exists) => fn(string $blob_path) => $exists(new \nostriphant\Blossom\Blob($blob_path));
        $blob_missing = fn(callable $missing) => fn(string $blob_path) => $missing(new \nostriphant\Blossom\Blob\Missing($blob_path));
        
        $this->blob_factory = fn(callable $exists, callable $missing) => fn(string $hash) => new \nostriphant\Functional\When(
                                'file_exists', 
                                $blob_existing($exists), 
                                $blob_missing($missing)
                        )($path . DIRECTORY_SEPARATOR . $hash);
    }
    
    #[\Override]
    public function __invoke(callable $define) : void {
        $redefine = fn(string $method, callable $handler) => $define($method, '/{hash:\w+}[.{ext:\w+}]', fn(array $attributes) => ($this->blob_factory)($handler, fn() => ['status' => 404])($attributes['hash']));
        new Blob\Options($redefine);
        new Blob\Get($redefine);
    }
}
