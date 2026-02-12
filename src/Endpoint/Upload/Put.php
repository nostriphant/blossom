<?php

namespace nostriphant\Blossom\Endpoint\Upload;


readonly class Put {
    
    public function __construct(callable $define) {
        $define('PUT', $this);
    }
    
    public function __invoke(\nostriphant\Blossom\Blob\Creatable|\nostriphant\Blossom\Blob $blob, callable $stream) : array {
        if ($blob instanceof \nostriphant\Blossom\Blob) {
            return ['status' => 409];
        }
        
        
        $new_blob = $blob($stream);
        $content = json_encode([
                "url" => $new_blob->url,
                "sha256" => $new_blob->sha256,
                "size" => $new_blob->size,
                "type" => $new_blob->type,
                "uploaded" => $new_blob->uploaded
            ]);
        
        return [
            'status' => 201,
            'headers' => [
                'Access-Control-Allow-Origin' => '*',   
                'Content-Type' => 'application/json',
                'Content-Length' => strlen($content),
                'Content-Location' => '/' . $new_blob->sha256
            ],
            'body' => $content
        ];
    }
}
