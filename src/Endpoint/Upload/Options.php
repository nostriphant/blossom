<?php

namespace nostriphant\Blossom\Endpoint\Upload;

readonly class Options {
    public function __invoke(\nostriphant\Blossom\Blob\Uncreated $blob) : array {
        return [
            'status' => '204',
            'headers' => [
                'Access-Control-Allow-Origin' => 'Authorization, *',
                'Access-Control-Allow-Methods' => 'PUT',
                'Access-Control-Max-Age' => 86400
            ]
        ];
    }
}
