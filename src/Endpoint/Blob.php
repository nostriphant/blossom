<?php

namespace nostriphant\Blossom\Endpoint;

use nostriphant\Blossom\Endpoint;

readonly class Blob implements Endpoint {
    
    private \Closure $blob_factory;
    
    public function __construct(string $path) {
        $this->blob_factory = fn(callable $handler, string $hash) => new \nostriphant\Functional\When(
                                'file_exists', 
                                fn(string $blob_path) => $handler(new \nostriphant\Blossom\Blob($blob_path)), 
                                fn(string $blob_path) => ['status' => 404],
                        )($path . DIRECTORY_SEPARATOR . $hash);
    }
    
    #[\Override]
    public function __invoke(callable $define) : void {
        $redefine = fn(string $method, callable $handler) => $define($method, '/{hash:\w+}[.{ext:\w+}]', fn(array $attributes) => ($this->blob_factory)($handler, $attributes['hash']));
        new Blob\Options($redefine);
        new Blob\Get($redefine);
    }
}
