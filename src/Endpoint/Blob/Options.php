<?php

namespace nostriphant\Blossom\Endpoint\Blob;

readonly class Options {
    
    public function __construct(private \nostriphant\Blossom\Blob $blob) {

    }
    
    public function __invoke() : array {
        return ($this->blob)(
            fn(\nostriphant\Blossom\Blob $blob) => [
                'status' => '204',
                'headers' => [
                    'Access-Control-Allow-Origin' => 'Authorization, *',
                    'Access-Control-Allow-Methods' => 'GET, HEAD',
                    'Access-Control-Max-Age' => 86400
                ]
            ], 
            fn() => ['status' => 404]
        );
    }
}
