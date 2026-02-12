<?php

namespace nostriphant\Blossom\Endpoint\Upload;


readonly class Put {
    
    private \nostriphant\Blossom\Blob $blob;
    
    public function __construct(\nostriphant\Blossom\Blob\Uncreated $blob, callable $stream) {
        $this->blob = $blob($stream);
    }
    public function __invoke() : array {
        $content = json_encode([
                "url" => $this->blob->url,
                "sha256" => $this->blob->sha256,
                "size" => $this->blob->size,
                "type" => $this->blob->type,
                "uploaded" => $this->blob->uploaded
            ]);
        
        return [
            'status' => 201,
            'headers' => [
                'Access-Control-Allow-Origin' => '*',   
                'Content-Type' => 'application/json',
                'Content-Length' => strlen($content),
                'Content-Location' => '/' . $this->blob->sha256
            ],
            'body' => $content
        ];
    }
}
