<?php

namespace nostriphant\Blossom\Endpoint\Upload;

readonly class Head implements \nostriphant\Blossom\Endpoint\Action {

    
    private \Closure $upload_authorized;
    
    public function __construct(callable $upload_authorized, private \nostriphant\Blossom\Blob\Uncreated $blob, private mixed $stream) {
        $this->upload_authorized = \Closure::fromCallable($upload_authorized);
    }
    
    public function authorize(\nostriphant\NIP01\Event $authorization_event, array $additional_headers, callable $action, callable $unauthorized) : array {
        if (isset($additional_headers['X_CONTENT_TYPE'], $additional_headers['X_CONTENT_LENGTH'], $additional_headers['X_SHA_256']) === false) {
            return $unauthorized(400, 'Mssing X-Content-Type, X-Content-Length or X-SHA-256 headers');
        }
        
        
        return $action(fn(string $pubkey_hex) => ($this->upload_authorized)(
                $pubkey_hex, 
                $additional_headers['X_CONTENT_LENGTH'] ?? -1, 
                $additional_headers['X_CONTENT_TYPE'] ?? "application/octet-stream", 
                fn(string $pubkey_hex) => ['status' => 200],
                $unauthorized
        ));
    }
}
