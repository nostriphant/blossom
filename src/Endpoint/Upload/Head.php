<?php

namespace nostriphant\Blossom\Endpoint\Upload;

readonly class Head implements \nostriphant\Blossom\Endpoint\Action {

    private \Closure $upload_authorized;
    private \Closure $stream;

    public function __construct(callable $upload_authorized, private \nostriphant\Blossom\Blob\Uncreated $blob, callable $stream) {
        $this->stream = \Closure::fromCallable($stream);
        $this->upload_authorized = \Closure::fromCallable($upload_authorized);
    }

    public function authorize(\nostriphant\NIP01\Event $authorization_event, array $additional_headers, callable $action, callable $unauthorized) : array {
        if (isset($additional_headers['X_CONTENT_TYPE'], $additional_headers['X_CONTENT_LENGTH'], $additional_headers['X_SHA_256']) === false) {
            return $unauthorized(400, 'Mssing X-Content-Type, X-Content-Length or X-SHA-256 headers');
        }
        
        $result = call_user_func($this->upload_authorized, $authorization_event->pubkey, $additional_headers['X_CONTENT_LENGTH'], $additional_headers['X_CONTENT_TYPE'], $unauthorized);
        return $result === true ? $action() : $result;
    }

    #[\Override]
    public function __invoke(string $pubkey_hex): array {
        $blob = ($this->blob)($pubkey_hex, $this->stream);

        return [
            'status' => 200
        ];
    }
}
