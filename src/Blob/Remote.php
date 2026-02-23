<?php

namespace nostriphant\Blossom\Blob;


class Remote {
    
    public function __construct(private \nostriphant\Blossom\VFS\Directory $directory) {
    }
    
    public function __invoke(string $pubkey_hex, mixed $stream, string $hash): \nostriphant\Blossom\Blob {
        try {
            return new \nostriphant\Blossom\Blob\Created(($this->directory)($pubkey_hex, $stream, $hash));
        } catch (\nostriphant\Blossom\Exception $e) {
            return Failed::fromException($e);
        }
    }
    
}
