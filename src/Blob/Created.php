<?php

namespace nostriphant\Blossom\Blob;

readonly class Created extends \nostriphant\Blossom\Blob {
    
    
    
    public function __invoke(): array {
        return [
            'status' => 201,
            'headers' => [
                'Content-Type' => 'application/json',
                'Content-Location' => '/' . $this->sha256
            ],
            'body' => json_encode([
                "url" => $this->url,
                "sha256" => $this->sha256,
                "size" => $this->size,
                "type" => $this->type,
                "uploaded" => $this->uploaded
            ])
        ];
    }
    
}
