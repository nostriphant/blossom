<?php

namespace nostriphant\Blossom\Endpoint;

use nostriphant\Blossom\Endpoint;

readonly class Blob implements Endpoint {
    
    public function __construct(private string $path) {

    }
    
    #[\Override]
    public function __invoke(callable $define) : void {
        $redefine = fn(string $method, callable $exists) => $define($method, '/{hash:\w+}[.{ext:\w+}]', fn(array $attributes) => (new \nostriphant\Blossom\Blob($this->path . DIRECTORY_SEPARATOR . $attributes['hash'], $exists, fn() => ['status' => 404]))());
        new Blob\Options($redefine);
        new Blob\Get($redefine);
    }
}
