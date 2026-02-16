<?php

namespace nostriphant\Blossom\Endpoint\Action;

readonly class Options {
    private array $methods;
    public function __construct(\nostriphant\Blossom\HTTP\Method ...$methods) {
        $this->methods = array_map(fn(\nostriphant\Blossom\HTTP\Method $method) => $method->name, $methods);
    }
    
    public function __invoke() : array {
        return [
            'status' => '204',
            'headers' => [
                'Access-Control-Allow-Origin' => 'Authorization, *',
                'Access-Control-Allow-Methods' => join(', ', $this->methods),
                'Access-Control-Max-Age' => 86400
            ]
        ];
    }
}
