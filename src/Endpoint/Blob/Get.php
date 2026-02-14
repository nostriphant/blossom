<?php

namespace nostriphant\Blossom\Endpoint\Blob;


readonly class Get implements \nostriphant\Blossom\Endpoint {
    public function __construct(private \nostriphant\Blossom\Blob $blob) {}
    public function __invoke(\nostriphant\NIP01\Event $authorization_event) : array {
        if (\nostriphant\NIP01\Event::hasTag($authorization_event, 'x') === false) {
            return ['status' => 401];
        } elseif (\nostriphant\NIP01\Event::extractTagValues($authorization_event, 'x')[0][0] !== $this->blob->sha256) {
            return ['status' => 401];
        } elseif ($this->blob->exists === false) {
            return ['status' => 404];
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
