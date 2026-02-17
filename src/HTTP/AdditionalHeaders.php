<?php


namespace nostriphant\Blossom\HTTP;

class AdditionalHeaders {
    
    public function __invoke(array $response): array {
        $additional_headers = ['Access-Control-Allow-Origin' => '*'];
        if (isset($response['body']) === false) {
        } elseif(isset($headers['Content-Length']) === false) {
            $additional_headers['Content-Length'] = strlen($response['body']);
        }

        $response['headers'] = array_merge($additional_headers, $response['headers'] ?? []);

        return $response;
    }
    
}
