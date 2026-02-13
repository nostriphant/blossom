<?php

namespace nostriphant\Blossom\Endpoint;

readonly class Blob implements Factory {
    
    
    public function __construct(private string $path) {
    }
    
    #[\Override]
    public function attributes(array $attributes, callable $stream) : array {
        return [
            new \nostriphant\Blossom\Blob($this->path . DIRECTORY_SEPARATOR . $attributes['hash'])
        ];
    }
    
    #[\Override]
    public function __invoke(callable $define) : void {
        $define(\nostriphant\Blossom\Method::GET, fn(\nostriphant\Blossom\Blob $blob) => new Blob\Get($blob));
        $define(\nostriphant\Blossom\Method::DELETE, fn(\nostriphant\Blossom\Blob $blob) => new Blob\Delete($blob));
    }
}
