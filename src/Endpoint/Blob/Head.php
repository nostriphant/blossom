<?php

namespace nostriphant\Blossom\Endpoint\Blob;

use nostriphant\Blossom\Endpoint;

readonly class Head implements Endpoint {
    
    public function __construct(private string $path) {

    }
    
    #[\Override]
    public function __invoke(callable $define) : void {
        $define('HEAD', '/{hash:\w+}', fn(array $attributes) => (new \nostriphant\Blossom\Blob($this->path . DIRECTORY_SEPARATOR . $attributes['hash']))(
            fn(\nostriphant\Blossom\Blob $blob) => [
                'headers' => [
                    'Content-Type' => $blob->type,
                    'Access-Control-Allow-Origin' => '*',
                    'Content-Length' => $blob->size
                ]
            ], 
            fn() => ['status' => 404]
        ));
    }
}
