<?php


namespace nostriphant\Blossom\Endpoint;

interface Action {
    public function __invoke(\nostriphant\NIP01\Event $authorization_event, array $additional_headers, callable $action, callable $unauthorized) : array;
}
