<?php

namespace nostriphant\Blossom\Endpoint\Root;


readonly class Get implements \nostriphant\Blossom\Endpoint\Action {
    public function __construct() {}
    
    public function __invoke(?\nostriphant\NIP01\Event $authorization_event, array $additional_headers, callable $action, callable $unauthorized) : array {
        return $action(fn() => ['status' => 200]);
    }
}
