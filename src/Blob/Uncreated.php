<?php

namespace nostriphant\Blossom\Blob;


class Uncreated {
    
    private \Closure $url_register;
    
    public function __construct(private \nostriphant\Blossom\VFS\Directory $directory, callable $url_register) {
        $this->url_register = \Closure::fromCallable($url_register);
    }
    
    public function __invoke(string $pubkey_hex, mixed $stream, string $hash, ?string $uri) : array {
        try {
            $file = ($this->directory)($pubkey_hex, $stream, $hash);
            $blob = new \nostriphant\Blossom\Blob($file, ($this->url_register)($hash, $uri));
            return [
                'status' => 201,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Content-Location' => '/' . $blob->sha256
                ],
                'body' => $blob
            ];
        } catch (\nostriphant\Blossom\Exception $e) {
            return ['status' => $e->getCode(), 'headers' => ['x-reason' => $e->getMessage()]];
        }
    }
    
}
