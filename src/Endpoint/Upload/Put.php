<?php

namespace nostriphant\Blossom\Endpoint\Upload;

readonly class Put implements \nostriphant\Blossom\Endpoint\Action {

    private \Closure $upload_authorized;
    
    public function __construct(callable $upload_authorized, private \nostriphant\Blossom\Blob\Uncreated $blob, private mixed $stream) {
        $this->upload_authorized = \Closure::fromCallable($upload_authorized);
    }
    
    public function authorize(\nostriphant\NIP01\Event $authorization_event, array $additional_headers, callable $action, callable $unauthorized) : array {
        $result = call_user_func($this->upload_authorized, $authorization_event->pubkey, $additional_headers['CONTENT_LENGTH'] ?? -1, $additional_headers['CONTENT_TYPE'] ?? "application/octet-stream", $unauthorized);
        return $result === true ? $action(fn(string $pubkey_hex): array => ($this->blob)($pubkey_hex, $this->stream, \nostriphant\NIP01\Event::extractTagValues($authorization_event, 'x')[0][0])) : $result;
    }
}
