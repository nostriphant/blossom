<?php

namespace nostriphant\Blossom\Blob;


class Uncreated {
    
    public function __construct(private \nostriphant\Blossom\VFS\Directory $directory) {
    }
    
    public function __invoke(string $pubkey_hex, mixed $stream, string $hash) : array {
        try {
            $blob = new \nostriphant\Blossom\Blob(($this->directory)($pubkey_hex, $stream, $hash));
            return [
                'status' => 201,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Content-Location' => '/' . $blob->sha256
                ],
                'body' => json_encode([
                    "url" => $blob->url,
                    "sha256" => $blob->sha256,
                    "size" => $blob->size,
                    "type" => $blob->type,
                    "uploaded" => $blob->uploaded
                ])
            ];
        } catch (\nostriphant\Blossom\Exception $e) {
            return ['status' => $e->getCode(), 'headers' => ['x-reason' => $e->getMessage()]];
        }
    }
    
}
