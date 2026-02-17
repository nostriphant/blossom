<?php

namespace nostriphant\Blossom\Endpoint\Upload;

readonly class Put implements \nostriphant\Blossom\Endpoint\Action {

    private Head $head;
    private \Closure $stream;
    
    public function __construct(callable $upload_authorized, private \nostriphant\Blossom\Blob\Uncreated $blob, callable $stream) {
        $this->stream = \Closure::fromCallable($stream);
        $this->head = new Head($upload_authorized, $blob, $stream);
    }
    
    public function authorize(\nostriphant\NIP01\Event $authorization_event) : bool {
        return $this->head->authorize($authorization_event);
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
