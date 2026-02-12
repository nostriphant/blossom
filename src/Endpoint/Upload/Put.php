<?php

namespace nostriphant\Blossom\Endpoint\Upload;


readonly class Put {
    
    private \Closure $stream;
    
    public function __construct(private \nostriphant\Blossom\Blob\Uncreated $blob, callable $stream) {
        $this->stream = \Closure::fromCallable($stream);
    }
    public function __invoke() : array {
        $blob = ($this->blob)($this->stream);
        
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
