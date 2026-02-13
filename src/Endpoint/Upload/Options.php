<?php

namespace nostriphant\Blossom\Endpoint\Upload;

readonly class Options {
    private array $methods;
    public function __construct(\nostriphant\Blossom\Method ...$methods) {
        $this->methods = $methods;
    }
    
    public function __invoke() : array {
        return [
            'status' => '204',
            'headers' => [
                'Access-Control-Allow-Origin' => 'Authorization, *',
                'Access-Control-Allow-Methods' => join(',', array_map(fn(\nostriphant\Blossom\Method $method) => $method->name, $this->methods)),
                'Access-Control-Max-Age' => 86400
            ]
        ];
    }
}
