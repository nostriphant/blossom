<?php

namespace nostriphant\Blossom\Endpoint\Blob;


readonly class Delete {
    public function __construct(private \nostriphant\Blossom\Blob $blob) {}
    public function __invoke() : array {
        if ($this->blob->exists === false) {
            return ['status' => 200];
        }
        
        \nostriphant\Blossom\Blob::delete($this->blob);
        
        return ['status' =>  204];
    }
}
