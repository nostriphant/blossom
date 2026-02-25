<?php

namespace nostriphant\Blossom\Endpoint\Upload;
use \nostriphant\Functional\Partial;

readonly class Head implements \nostriphant\Blossom\Endpoint\Action {

    public function __construct(
            private \nostriphant\Blossom\UploadConstraints $upload_authorized, 
            private \nostriphant\Blossom\Blob\Uncreated $blob, 
            private mixed $stream
    ) {
    }
    
    public function __invoke(\nostriphant\NIP01\Event $authorization_event, array $additional_headers, callable $action, callable $unauthorized) : array {
        if (isset($additional_headers['X_CONTENT_TYPE'], $additional_headers['X_CONTENT_LENGTH'], $additional_headers['X_SHA_256']) === false) {
            return $unauthorized(400, 'Mssing X-Content-Type, X-Content-Length or X-SHA-256 headers');
        }
        
        
        return $action(Partial::right($this->upload_authorized,
                $additional_headers['X_CONTENT_LENGTH'] ?? -1, 
                $additional_headers['X_CONTENT_TYPE'] ?? "application/octet-stream", 
                fn(string $pubkey_hex) => ['status' => 200],
                $unauthorized
        ));
    }
}
