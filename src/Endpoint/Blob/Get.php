<?php

namespace nostriphant\Blossom\Endpoint\Blob;


readonly class Get {
    
    public function __construct(callable $define, string $path) {
        $define('GET', \nostriphant\Blossom\Endpoint\Blob::blob($path, $this));
    }
    
    public function __invoke(\nostriphant\Blossom\Blob $blob) : array {
        return [
            'headers' => [
                'Access-Control-Allow-Origin' => '*',   
                'Content-Type' => $blob->type,
                'Content-Length' => $blob->size
            ],
            'body' => $blob->contents
        ];
    }
}
