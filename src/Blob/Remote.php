<?php

namespace nostriphant\Blossom\Blob;


class Remote {
    
    private \Closure $unsupported_media_types;
    
    public function __construct(private \nostriphant\Blossom\VFS\Directory $directory, callable $unsupported_media_types) {
        $this->unsupported_media_types = \Closure::fromCallable($unsupported_media_types);
    }
    
    public function __invoke(string $pubkey_hex, \nostriphant\NIP01\Key $server_key, string $url): \nostriphant\Blossom\Blob {
        
        list($hash, ) = explode('.', basename(parse_url($url, PHP_URL_PATH)));
        $authorization_rumor = new \nostriphant\NIP01\Rumor(time(), $server_key(\nostriphant\NIP01\Key::public()), 24242,  'Mirroring ' . $url, [
            ['t', 'get'],
            ["expiration", time() + 3600],
            ['x', $hash]
        ]);
        $authorization_event = $authorization_rumor($server_key);
        $headers[] = 'Authorization: Nostr ' . base64_encode(\nostriphant\NIP01\Nostr::encode($authorization_event()));
        $handle_remote = fopen($url, 'rb', context: stream_context_create(['http' => [
            'method' => 'GET',
            'header' => join("\r\n", $headers) . "\r\n"
        ]]));
        
        if ($handle_remote === false) {
            return new \nostriphant\Blossom\Blob\Failed(500, 'Unable to open remote location.');
        }
        
        
        foreach(new \nostriphant\Blossom\HTTP\HeaderStruct(http_get_last_response_headers()) as $header => $value) {
            switch ($header) {
                case 'content-length':
                    if ($value[0] > $this->directory->max_file_size) {
                        return new \nostriphant\Blossom\Blob\Failed(413, 'Filesize of remote file seems larger than max allowed file size.');
                    }
                    break;
                case 'content-type':
                    if (call_user_func($this->unsupported_media_types, $value[0])) {
                        return new \nostriphant\Blossom\Blob\Failed(415, 'Unsupported file type "' . $value . '".');
                    }
                    break;
            }
        }
        
        $temp = tempnam($this->path, "buffer.");
        try {
            $file = ($this->directory)($pubkey_hex, $handle_remote, $hash);
            fclose($handle_remote);
            return new \nostriphant\Blossom\Blob\Created($file);
        } catch (\nostriphant\Blossom\Exception $e) {
            return Failed::fromException($e);
        }
    }
    
}
