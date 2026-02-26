<?php

namespace nostriphant\Blossom;

use nostriphant\NIP01\Key;

function request(string $method, string $uri, $upload_resource = null, ?array $authorization = null, ?array $headers = []) : array {
    $curl = curl_init($uri);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    
    if (isset($authorization)) {
        $sender_key = Key::fromHex($authorization['key'] ?? 'a71a415936f2dd70b777e5204c57e0df9a6dffef91b3c78c1aa24e54772e33c3');
        unset($authorization['key']);
        $sender_pubkey = $authorization['pubkey'] ?? $sender_key(Key::public()); // 15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f
        unset($authorization['pubkey']);
        
        $tags = [];
        if (isset($authorization['expiration']) === false) {
            $tags[] = ["expiration", time() + 3600];
        }
        foreach ($authorization as $tag => $value) {
            $tags[] = [$tag, $value];
        }
        
        $authorization_rumor = new \nostriphant\NIP01\Rumor(time(), $sender_pubkey, 24242, $method . ' ' . $uri, $tags);
        $authorization_event = $authorization_rumor($sender_key);
        $headers[] = 'Authorization: Nostr ' . base64_encode(\nostriphant\NIP01\Nostr::encode($authorization_event()));
    }
    
    switch ($method) {
        case 'HEAD':
            curl_setopt($curl, CURLOPT_NOBODY, true);
            break;
        case 'PUT':
            if (is_string($upload_resource)) {
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $upload_resource);
                $headers[] = 'Content-Type:application/json';
            } else {
                curl_setopt($curl, CURLOPT_UPLOAD, 1);
                curl_setopt($curl, CURLOPT_READDATA, $upload_resource);
                curl_setopt($curl, CURLOPT_READFUNCTION, fn($ch, $fh, int $length) => fread($fh, $length));
            }
            break;
        default:
            break;

    }
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $raw_response = curl_exec($curl);
    if ($raw_response === false) {
        $error = curl_error($curl);
        curl_close($curl);
        sleep(1);
        return request(...func_get_args());
    }
    $info = curl_getinfo($curl);
    curl_close($curl);

    
    $headers = new HTTP\HeaderStruct(explode("\r\n", substr($raw_response, 0, $info['header_size'])));
    $response_body = substr($raw_response, $info['header_size']);
    
    if (isset($headers['status']) === false) {
        throw new \Exception(var_export($raw_response, true));
    }
    
    return [$headers['protocol'], $headers['status'], $headers, $response_body];
}

function writeFile(string $directory, string $content) : string {
    $hash = hash('sha256', $content);
    file_put_contents($directory . DIRECTORY_SEPARATOR . $hash, $content);
    return $hash;
}
function deleteFile(string $directory, string $hash) : bool {
    return unlink($directory . DIRECTORY_SEPARATOR . $hash);
}