<?php

namespace nostriphant\Blossom\HTTP;

readonly class ServerRequest {
    
    public function __construct(
            public array $headers,
            public array $attributes,
            private mixed $body
    ) {
        
    }
    
    public function __invoke(): \Generator {
        yield from (match (gettype($this->body)) {
            'string' => function(string $content) { yield $content; },
            'resource' => function($handle) {
                while (feof($handle) === false) {
                    yield fread($handle, 1024);
                }
            },
            default => null
        })($this->body);
    }
}
