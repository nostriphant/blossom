<?php

namespace nostriphant\Blossom\Endpoint\Blob;


readonly class Get {
    public function __construct(private \nostriphant\Blossom\Blob $blob) {}
    public function __invoke() : array {
        return [
            'headers' => [
                'Access-Control-Allow-Origin' => '*',   
                'Content-Type' => $this->blob->type,
                'Content-Length' => $this->blob->size
            ],
            'body' => $this->blob->contents
        ];
    }
}
