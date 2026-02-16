<?php

namespace nostriphant\Blossom\Endpoint\Upload;


readonly class Put implements \nostriphant\Blossom\Endpoint\Action {
    
    private \Closure $stream;
    
    public function __construct(private \nostriphant\Blossom\Blob\Uncreated $blob, callable $stream) {
        $this->stream = \Closure::fromCallable($stream);
    }
    public function __invoke(\nostriphant\NIP01\Event $authorization_event) : array {
        $blob = ($this->blob)($this->stream);
        
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
    }
}
