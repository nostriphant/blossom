<?php

namespace nostriphant\Blossom\Endpoint\Mirror;
use \nostriphant\Functional\Partial;

readonly class Put implements \nostriphant\Blossom\Endpoint\Action {

    public function __construct(
            private \nostriphant\Blossom\UploadConstraints $upload_authorized, 
            private \nostriphant\Blossom\Blob\Uncreated $blob, 
            private \nostriphant\NIP01\Key $server_key, 
            private \Traversable $stream
    ) {
    }

    public function __invoke(\nostriphant\NIP01\Event $authorization_event, array $additional_headers, callable $action, callable $unauthorized) : array {
        $body = '';
        foreach ($this->stream as $buffer) {
            $body .= $buffer;
        }
        $json = json_decode($body);
        if (is_null($json)) {
            return $unauthorized(400, 'Unparsable authorization event.');
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
            return $unauthorized(500, 'Unable to open remote location.');
        }
        
        $headers = new \nostriphant\Blossom\HTTP\HeaderStruct(http_get_last_response_headers());
        return $action(Partial::right($this->upload_authorized,
                $headers['content-length'][0] ?? -1, $headers['content-type'][0] ?? '', 
                Partial::right($this->blob, $handle_remote, $hash),
                $unauthorized
        ));
    }
}
