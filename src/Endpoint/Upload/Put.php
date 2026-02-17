<?php

namespace nostriphant\Blossom\Endpoint\Upload;

readonly class Put implements \nostriphant\Blossom\Endpoint\Action {

    private \Closure $upload_authorized;
    private \Closure $stream;
    
    public function __construct(callable $upload_authorized, private \nostriphant\Blossom\Blob\Uncreated $blob, callable $stream) {
        $this->upload_authorized = \Closure::fromCallable($upload_authorized);
        $this->stream = \Closure::fromCallable($stream);
    }
    
    public function authorize(\nostriphant\NIP01\Event $authorization_event, array $additional_headers, callable $action, callable $unauthorized) : array {
        $result = call_user_func($this->upload_authorized, $authorization_event->pubkey, $additional_headers, $unauthorized);
        return $result === true ? $action() : $result;
    }
    
    #[\Override]
    public function __invoke(string $pubkey_hex): array {
        $blob = ($this->blob)($pubkey_hex, $this->stream);

        return [
            'status' => 201,
            'headers' => [
                'Content-Type' => 'application/json',
                'Content-Location' => '/' . $blob->sha256
            ],
            'body' => json_encode([
                "url" => $blob->url,
                "sha256" => $blob->sha256,
                "size" => $blob->size,
                "type" => $blob->type,
                "uploaded" => $blob->uploaded
            ])
        ];
    }
}
