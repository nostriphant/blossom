<?php

namespace nostriphant\Blossom\Endpoint;

use nostriphant\Blossom\Endpoint;

readonly class Blob implements Endpoint {
    
    public function __construct(private \nostriphant\Blossom\Blob\Factory $blob_factory) {

    }
    
    #[\Override]
    public function __invoke(callable $define) : void {
        $redefine = fn(string $method, callable $exists) => $define($method, '/{hash:\w+}[.{ext:\w+}]', fn(array $attributes) => ($this->blob_factory)($exists)($attributes['hash']));
        new Blob\Options($redefine);
        new Blob\Get($redefine);
    }
}
