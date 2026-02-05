<?php

namespace nostriphant\Blossom\Endpoint\Blob;

use nostriphant\Blossom\Endpoint;

readonly class Options implements Endpoint {
    
    public function __construct(private string $path) {

    }
    
    #[\Override]
    public function __invoke(callable $define) : void {
        $define('OPTIONS', '/{hash:\w+}', fn(array $attributes) => (new \nostriphant\Blossom\Blob($this->path . DIRECTORY_SEPARATOR . $attributes['hash']))(
            fn(\nostriphant\Blossom\Blob $blob) => [
                'status' => '204',
                'headers' => [
                    'Access-Control-Allow-Origin' => 'Authorization, *',
                    'Access-Control-Allow-Methods' => 'GET, HEAD',
                    'Access-Control-Max-Age' => 86400
                ]
            ], 
            fn() => ['status' => 404]
        ));
    }
}
