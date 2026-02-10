<?php

namespace nostriphant\Blossom\Endpoint\Blob;

readonly class Options {
    
    public function __construct(callable $define, string $path) {
        $define('OPTIONS', \nostriphant\Blossom\Endpoint\Blob::blob($path, $this));
    }
    
    public function __invoke(\nostriphant\Blossom\Blob $blob) : array {
        return [
            'status' => '204',
            'headers' => [
                'Access-Control-Allow-Origin' => 'Authorization, *',
                'Access-Control-Allow-Methods' => 'GET, HEAD',
                'Access-Control-Max-Age' => 86400
            ]
        ];
    }
}
