<?php

namespace nostriphant\Blossom\Blob;


class Remote {
    
    private \Closure $unsupported_media_types;
    
    public function __construct(private string $path, private ?int $max_file_size, callable $unsupported_media_types) {
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
                    if ($value[0] > $this->max_file_size) {
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
        
        $written = 0;
        $handle = fopen($temp, 'wb');
        while (feof($handle_remote) === false) {
            $chunk = fread($handle_remote, 1024);
            $written += fwrite($handle, $chunk);
            if ($written > $this->max_file_size) {
                fclose($handle);
                unlink($temp);
                return new \nostriphant\Blossom\Blob\Failed(413, 'Filesize of remote file larger than max allowed file size.');
            }
        }
        fclose($handle_remote);
        fclose($handle);
        
        $target_location = $this->path . DIRECTORY_SEPARATOR . hash_file('sha256', $temp);
        if (file_exists($target_location) === false) {
            rename($temp, $target_location);
            mkdir($target_location . '.owners');
        }
        
        touch($target_location . '.owners' . DIRECTORY_SEPARATOR . $pubkey_hex);
        
        return new \nostriphant\Blossom\Blob($target_location);
    }
    
}
