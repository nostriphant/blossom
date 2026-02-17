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
    
    public function __invoke(string $pubkey_hex) : array {
        if ($this->blob->exists === false) {
            return ['status' => 200];
        } elseif (in_array($pubkey_hex, $this->blob->owners) === false) {
            return ['status' => 403];
        }
        
        \nostriphant\Blossom\Blob::delete($this->blob, $pubkey_hex);
        
        return ['status' =>  204];
    }
}
