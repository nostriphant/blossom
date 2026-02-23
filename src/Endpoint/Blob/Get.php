<?php

namespace nostriphant\Blossom\Endpoint\Blob;


readonly class Get implements \nostriphant\Blossom\Endpoint\Action {
    public function __construct(private \nostriphant\Blossom\Blob $blob) {}
    
    public function authorize(\nostriphant\NIP01\Event $authorization_event, array $additional_headers, callable $action, callable $unauthorized) : array {
        if (\nostriphant\NIP01\Event::hasTag($authorization_event, 'x') === false) {
            return $unauthorized(401, 'Missing x-tag in authorization event');
        } elseif (\nostriphant\NIP01\Event::extractTagValues($authorization_event, 'x')[0][0] !== $this->blob->sha256) {
            return $unauthorized(401, 'x-tag does not match blob sha256 (' . $this->blob->sha256 .')');
        }
        return $action();
    }
    
    #[\Override]
    public function __invoke(string $pubkey_hex, array $args) : array {
        if ($this->blob->exists === false) {
            return ['status' => 404];
        }
        return ($this->blob)();
    }
}
