<?php

namespace nostriphant\Blossom\Endpoint\Mirror;

readonly class Put implements \nostriphant\Blossom\Endpoint\Action {

    private \Closure $upload_authorized;

    public function __construct(callable $upload_authorized, private \nostriphant\Blossom\Blob\Uncreated $blob, private \nostriphant\NIP01\Key $server_key, private \Traversable $stream) {
        $this->upload_authorized = \Closure::fromCallable($upload_authorized);
    }

    public function authorize(\nostriphant\NIP01\Event $authorization_event): bool {
        return call_user_func($this->upload_authorized, $authorization_event->pubkey);
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

        
        list($hash, ) = explode('.', basename(parse_url($json->url, PHP_URL_PATH)));
        $authorization_rumor = new \nostriphant\NIP01\Rumor(time(), ($this->server_key)(\nostriphant\NIP01\Key::public()), 24242,  'Mirroring ' . $json->url, [
            ['t', 'get'],
            ["expiration", time() + 3600],
            ['x', $hash]
        ]);
        $authorization_event = $authorization_rumor($this->server_key);
        $headers[] = 'Authorization: Nostr ' . base64_encode(\nostriphant\NIP01\Nostr::encode($authorization_event()));
        $handle = fopen($json->url, 'rb', context: stream_context_create(['http' => [
            'method' => 'GET',
            'header' => join("\r\n", $headers) . "\r\n"
        ]]));
        if ($handle === false) {
            return ['status' => 500];
        }
        
        $blob = ($this->blob)($pubkey_hex, function() use ($handle) {
            while (feof($handle) === false) {
                $chunk = fread($handle, 1024);;
                
                error_log($chunk);
                yield $chunk;
            }
        });
        fclose($handle);

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
