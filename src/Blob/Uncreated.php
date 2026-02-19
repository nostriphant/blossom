<?php

namespace nostriphant\Blossom\Blob;


class Uncreated {
    
    private \Closure $unsupported_media_types;
    
    public function __construct(private \nostriphant\Blossom\VFS\Directory $directory, private string $target, callable $unsupported_media_types) {
        $this->unsupported_media_types = \Closure::fromCallable($unsupported_media_types);
    }
    
    public function __invoke(string $pubkey_hex, mixed $stream): \nostriphant\Blossom\Blob {
        try {
            return new \nostriphant\Blossom\Blob\Created(($this->directory)($pubkey_hex, $stream, $this->target));
        } catch (\nostriphant\Blossom\Exception $e) {
            return Failed::fromException($e);
        }
    }
    
}
