<?php

namespace nostriphant\Blossom\Blob;

class Factory {
    public function __construct(private string $path) {
        
    }
    public function __invoke(?string $hash = null): mixed {
        if (isset($hash) === false) {
            return new Uncreated($this->path);
        }
        return new \nostriphant\Blossom\Blob($this->path . DIRECTORY_SEPARATOR . $hash);
    }
}
