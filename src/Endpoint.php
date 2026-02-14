<?php


namespace nostriphant\Blossom;

interface Endpoint {
    
    public function __invoke(\nostriphant\NIP01\Event $authorization_event) : array;
}
