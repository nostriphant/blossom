<?php

namespace nostriphant\Blossom\Endpoint\Blobs;


readonly class Get implements \nostriphant\Blossom\Endpoint\Action {
    public function __construct(private array $blobs) {}
    
    public function __invoke(?\nostriphant\NIP01\Event $authorization_event, array $additional_headers, callable $action, callable $unauthorized) : array {
        if (isset($authorization_event) === false) {
        } elseif (\nostriphant\NIP01\Event::hasTag($authorization_event, 'x') === false) {
            return $unauthorized(401, 'Missing x-tag in authorization event');
        } elseif (\nostriphant\NIP01\Event::extractTagValues($authorization_event, 'x')[0][0] !== $this->blob->sha256) {
            return $unauthorized(401, 'x-tag does not match blob sha256 (' . $this->blob->sha256 .')');
        }
        return $action(fn() => [
            'status' => 200,
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($this->blobs)
        ]);
    }
}
