<?php

namespace nostriphant\Blossom\Endpoint\Mirror;

readonly class Put implements \nostriphant\Blossom\Endpoint\Action {

    private \Closure $upload_authorized;

    public function __construct(callable $upload_authorized, private \nostriphant\Blossom\Blob\Remote $blob, private \nostriphant\NIP01\Key $server_key, private \Traversable $stream) {
        $this->upload_authorized = \Closure::fromCallable($upload_authorized);
    }

    public function authorize(\nostriphant\NIP01\Event $authorization_event, array $additional_headers, callable $action, callable $unauthorized) : array {
        return call_user_func($this->upload_authorized, $authorization_event->pubkey) ? $action() : $unauthorized(401, '');
    }

    #[\Override]
    public function __invoke(string $pubkey_hex): array {
        $body = '';
        foreach ($this->stream as $buffer) {
            $body .= $buffer;
        }
        $json = json_decode($body);
        if (is_null($json)) {
            return ['status' => 400];
        }
        
        $blob = ($this->blob)($pubkey_hex, $this->server_key, $json->url);
        if ($blob->exists === false) {
            return ['status' => 500];
        }

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
