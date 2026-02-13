<?php

namespace nostriphant\Blossom\Endpoint;

readonly class Upload implements Factory {
    
    
    public function __construct(private string $path) {

    }
    
    #[\Override]
    public function attributes(array $attributes, callable $stream) : array {
        return [
            new \nostriphant\Blossom\Blob\Uncreated($this->path),
            $stream
        ];
    }
    
    #[\Override]
    public function __invoke(callable $define): void {
        $define(\nostriphant\Blossom\Method::PUT, fn(\nostriphant\Blossom\Blob\Uncreated $blob, callable $stream) => new Upload\Put($blob, $stream));
    }
}
