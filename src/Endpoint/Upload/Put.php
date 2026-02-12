<?php

namespace nostriphant\Blossom\Endpoint\Upload;


readonly class Put {
    
    public function __construct(private \nostriphant\Blossom\Blob\Uncreated $blob) {
    }
    public function __invoke(callable $stream) : array {
        $blob = ($this->blob)($stream);
        
        $content = json_encode([
                "url" => $blob->url,
                "sha256" => $blob->sha256,
                "size" => $blob->size,
                "type" => $blob->type,
                "uploaded" => $blob->uploaded
            ]);
        
        return [
            'status' => 201,
            'headers' => [
                'Access-Control-Allow-Origin' => '*',   
                'Content-Type' => 'application/json',
                'Content-Length' => strlen($content),
                'Content-Location' => '/' . $blob->sha256
            ],
            'body' => $content
        ];
    }
}
