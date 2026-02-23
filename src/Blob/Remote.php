<?php

namespace nostriphant\Blossom\Blob;


class Remote {
    
    private \Closure $unsupported_media_types;
    
    public function __construct(private \nostriphant\Blossom\VFS\Directory $directory, callable $unsupported_media_types) {
        $this->unsupported_media_types = \Closure::fromCallable($unsupported_media_types);
    }
    
    public function __invoke(string $pubkey_hex, $handle_remote, string $hash): \nostriphant\Blossom\Blob {
        try {
            $file = ($this->directory)($pubkey_hex, $handle_remote, $hash);
            fclose($handle_remote);
            return new \nostriphant\Blossom\Blob\Created($file);
        } catch (\nostriphant\Blossom\Exception $e) {
            return Failed::fromException($e);
        }
    }
    
}
