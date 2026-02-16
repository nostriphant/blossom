<?php

namespace nostriphant\Blossom\Endpoint\Blob;


readonly class Get implements \nostriphant\Blossom\Endpoint\Action {
    public function __construct(private \nostriphant\Blossom\Blob $blob) {}
    
    public function authorize(\nostriphant\NIP01\Event $authorization_event) : bool {
        if (\nostriphant\NIP01\Event::hasTag($authorization_event, 'x') === false) {
            return false;
        } elseif (\nostriphant\NIP01\Event::extractTagValues($authorization_event, 'x')[0][0] !== $this->blob->sha256) {
            return false;
        }
        return true;
    }
    
    public function __invoke(string $pubkey_hex) : array {
        if ($this->blob->exists === false) {
            return ['status' => 404];;
        }
        return [
            'headers' => [ 
                'Content-Type' => $this->blob->type,
                'Content-Length' => $this->blob->size
            ],
            'body' => $this->blob->contents
        ];
    }
}
