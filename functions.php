<?php

namespace nostriphant\Blossom;

function request(string $method, string $uri, ?string $body = null, array $headers = []) : array {
    $curl = curl_init($uri);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    switch ($method) {
        case 'HEAD':
            curl_setopt($curl, CURLOPT_NOBODY, true);
            break;
        default:
            break;

    }

    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $raw_response = curl_exec($curl);
    $info = curl_getinfo($curl);
    curl_close($curl);

    $response_headers = explode("\r\n", substr($raw_response, 0, $info['header_size']));
    list($protocol, $code) = explode(' ', array_shift($response_headers));
    $response_body = substr($raw_response, $info['header_size']);

    return [$protocol, $code, array_reduce($response_headers, function(array $carry, string $header) {
        if (empty($header)) {
            return $carry;
        }
        list($name, $value) = explode(":", $header, 2);
        $carry[strtolower($name)] = trim($value, " ");
        return $carry;
    } , []), $response_body];
}

function writeFile(string $directory, string $content) {
    $hash = hash('sha256', $content);
    file_put_contents($directory . DIRECTORY_SEPARATOR . $hash, $content);
    return $hash;
}