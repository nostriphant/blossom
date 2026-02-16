<?php


namespace nostriphant\Blossom\Endpoint;

interface Action {
    
    public function authorize(\nostriphant\NIP01\Event $authorization_event) : bool;
    
    public function __invoke(string $pubkey_hex) : array;
}
