<?php

namespace nostriphant\Blossom\Endpoint\Blob;


readonly class Get {
    
    public function __construct(private \nostriphant\Blossom\Blob $blob) {

    }
    
    public function __invoke() : array {
        return ($this->blob)(
            fn(\nostriphant\Blossom\Blob $blob) =>  [
                'headers' => [
                    'Access-Control-Allow-Origin' => '*',   
                    'Content-Type' => $blob->type,
                    'Content-Length' => $blob->size
                ],
                'body' => $blob->contents
            ], 
            fn() => ['status' => 404]
        );
    }
}
