<?php

namespace nostriphant\Blossom\Endpoint\Blob;


readonly class Delete implements \nostriphant\Blossom\Endpoint\Action {
    public function __construct(private \nostriphant\Blossom\Blob $blob) {}
    
    public function authorize(\nostriphant\NIP01\Event $authorization_event, array $additional_headers, callable $action, callable $unauthorized) : array {
        if (\nostriphant\NIP01\Event::hasTagValue($authorization_event, 'x', $this->blob->sha256) === false) {
            return $unauthorized(401, '');
        } elseif (\nostriphant\NIP01\Event::hasTagValue($authorization_event, 't', 'delete') === false) {
            return $unauthorized(401, '');
        }
        return $action();
    }
    
    #[\Override]
    public function __invoke(string $pubkey_hex, array $args) : array {
        try {
            \nostriphant\Blossom\Blob::delete($this->blob, $pubkey_hex);
            return ['status' =>  204];
        } catch (\nostriphant\Blossom\Exception $e) {
            return ['status' => $e->getCode(), 'headers' => ['x-reason' => $e->getMessage()]];
        }
    }
}
