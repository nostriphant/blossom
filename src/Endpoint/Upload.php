<?php

namespace nostriphant\Blossom\Endpoint;

use nostriphant\Blossom\Endpoint;

readonly class Upload implements Endpoint {
    
    public function __construct(private \nostriphant\Blossom\Blob\Factory $blob_factory) {

    }
    
    #[\Override]
    public function __invoke(callable $define) : void {
        $redefine = fn(string $method, callable $exists, callable $missing) => $define($method, '/upload', fn(array $attributes, callable $stream) => ($this->blob_factory)(fn(\nostriphant\Blossom\Blob\Creatable $blob) => $exists($blob, $stream), fn(\nostriphant\Blossom\Blob\Creatable $blob) => $missing($blob, $stream))());
        new Upload\Options($redefine);
        new Upload\Put($redefine);
    }
}
