<?php

namespace nostriphant\Blossom;

use nostriphant\NIP01\Key;

function request(string $method, string $uri, $upload_resource = null, ?array $authorization = null) : array {
    $curl = curl_init($uri);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    switch ($method) {
        case 'HEAD':
            curl_setopt($curl, CURLOPT_NOBODY, true);
            break;
        case 'PUT':
            curl_setopt($curl, CURLOPT_UPLOAD, 1);
            curl_setopt($curl, CURLOPT_READDATA, $upload_resource);
            curl_setopt($curl, CURLOPT_READFUNCTION, fn($ch, $fh, int $length) => fread($fh, $length));
            break;
        default:
            break;

    }
    
    $headers = [];
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
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $raw_response = curl_exec($curl);
    if ($raw_response === false) {
        throw new \Exception('Request to `'.$uri.'` failed: ' . curl_error($curl));
    }
    $info = curl_getinfo($curl);
    curl_close($curl);

    $response_headers = explode("\r\n", substr($raw_response, 0, $info['header_size']));
    list($protocol, $status) = explode(' ', array_shift($response_headers));
    $response_body = substr($raw_response, $info['header_size']);

    return [$protocol, $status, array_reduce($response_headers, function(array $carry, string $header) {
        if (empty($header)) {
            return $carry;
        }
        list($name, $value) = explode(":", $header, 2);
        $carry[strtolower($name)] = trim($value, " ");
        return $carry;
    } , []), $response_body];
}

function writeFile(string $directory, string $content) : string {
    $hash = hash('sha256', $content);
    file_put_contents($directory . DIRECTORY_SEPARATOR . $hash, $content);
    return $hash;
}
function deleteFile(string $directory, string $hash) : bool {
    return unlink($directory . DIRECTORY_SEPARATOR . $hash);
}