<?php

namespace nostriphant\Blossom\Endpoint\Blob;

readonly class Options {
    public function __invoke() : array {
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
