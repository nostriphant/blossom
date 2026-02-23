<?php

namespace nostriphant\Blossom\Endpoint\Upload;

readonly class Head extends Put implements \nostriphant\Blossom\Endpoint\Action {

    public function authorize(\nostriphant\NIP01\Event $authorization_event, array $additional_headers, callable $action, callable $unauthorized) : array {
        if (isset($additional_headers['X_CONTENT_TYPE'], $additional_headers['X_CONTENT_LENGTH'], $additional_headers['X_SHA_256']) === false) {
            return $unauthorized(400, 'Mssing X-Content-Type, X-Content-Length or X-SHA-256 headers');
        }
        
        $additional_headers['CONTENT_LENGTH'] = $additional_headers['X_CONTENT_LENGTH'];
        unset($additional_headers['X_CONTENT_LENGTH']);
        
        $additional_headers['CONTENT_TYPE'] = $additional_headers['X_CONTENT_TYPE'];
        unset($additional_headers['X_CONTENT_TYPE']);
        
        return parent::authorize($authorization_event, $additional_headers, fn(callable $paction) => $action(function(string $pubkey_hex) use ($paction): array {
            $response = $paction($pubkey_hex);

            return [
                'status' => 200
            ];
        }), $unauthorized);
    }
}
