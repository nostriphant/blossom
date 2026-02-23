<?php

namespace nostriphant\Blossom\Endpoint\Mirror;

readonly class Put implements \nostriphant\Blossom\Endpoint\Action {

    private \Closure $upload_authorized;

    public function __construct(callable $upload_authorized, private \nostriphant\Blossom\Blob\Remote $blob, private \nostriphant\NIP01\Key $server_key, private \Traversable $stream) {
        $this->upload_authorized = \Closure::fromCallable($upload_authorized);
    }

    public function authorize(\nostriphant\NIP01\Event $authorization_event, array $additional_headers, callable $action, callable $unauthorized) : array {
        $body = '';
        foreach ($this->stream as $buffer) {
            $body .= $buffer;
        }
        $json = json_decode($body);
        if (is_null($json)) {
            return ['status' => 400];
        }
        
        $url = $json->url;
        
        list($hash, ) = explode('.', basename(parse_url($url, PHP_URL_PATH)));
        $mirror_authorization_rumor = new \nostriphant\NIP01\Rumor(time(), ($this->server_key)(\nostriphant\NIP01\Key::public()), 24242,  'Mirroring ' . $url, [
            ['t', 'get'],
            ["expiration", time() + 3600],
            ['x', $hash]
        ]);
        $mirror_authorization_event = $mirror_authorization_rumor($this->server_key);
        $headers[] = 'Authorization: Nostr ' . base64_encode(\nostriphant\NIP01\Nostr::encode($mirror_authorization_event()));
        $handle_remote = fopen($url, 'rb', context: stream_context_create(['http' => [
            'method' => 'GET',
            'header' => join("\r\n", $headers) . "\r\n"
        ]]));
        
        if ($handle_remote === false) {
            return ['status'=> 500, 'reason' => 'Unable to open remote location.'];
        }
        
        $headers = new \nostriphant\Blossom\HTTP\HeaderStruct(http_get_last_response_headers());
        $result = call_user_func($this->upload_authorized, $authorization_event->pubkey, $headers['content-length'][0] ?? -1, $headers['content-type'][0] ?? '', $unauthorized);
        return $result === true ? $action(handle_remote:$handle_remote, hash:$hash) : $result;
    }

    #[\Override]
    public function __invoke(string $pubkey_hex, array $args): array {
        $blob = ($this->blob)($pubkey_hex, $args['handle_remote'], $args['hash']);
        fclose($args['handle_remote']);
        return $blob();
    }
}
