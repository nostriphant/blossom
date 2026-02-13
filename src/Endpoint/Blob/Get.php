<?php

namespace nostriphant\Blossom\Endpoint\Blob;


readonly class Get {
    public function __construct(private \nostriphant\Blossom\Blob $blob) {}
    public function __invoke() : array {
        if ($this->blob->exists === false) {
            return ['status' => 404];
        }
        
        return [
            'headers' => [ 
                'Content-Type' => $this->blob->type,
                'Content-Length' => $this->blob->size
            ],
            'body' => $this->blob->contents
        ];
    }
}
