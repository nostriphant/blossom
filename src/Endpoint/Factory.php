<?php


namespace nostriphant\Blossom\Endpoint;

class Factory {
    
    private \Closure $translate_request;
    
    public function __construct(callable $translate_request) {
        $this->translate_request = \Closure::fromCallable($translate_request);
    }
    
    public function __invoke(string $class) : \nostriphant\Blossom\Endpoint\Action\Factory {
        return new \nostriphant\Blossom\Endpoint\Action\Factory($class, $this->translate_request);
    }
}
