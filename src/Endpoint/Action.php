<?php


namespace nostriphant\Blossom\Endpoint;

interface Action {
    
    public function authorize(\nostriphant\NIP01\Event $authorization_event, array $additional_headers, callable $action, callable $unauthorized) : array;
    
    public function __invoke(string $pubkey_hex, array $args) : array;
}
