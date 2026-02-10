<?php

namespace nostriphant\Blossom\Endpoint;

use nostriphant\Blossom\Endpoint;

readonly class Blob implements Endpoint {
    
    public function __construct(private string $path) {

    }
    
    static function blob(string $path, callable $exists) : callable {
        return fn(array $attributes) => (new \nostriphant\Blossom\Blob($path . DIRECTORY_SEPARATOR . $attributes['hash'], $exists, fn() => ['status' => 404]))();
    }
    
    #[\Override]
    public function __invoke(callable $define) : void {
        $redefine = fn(string $method, callable $endpoint) => $define($method, '/{hash:\w+}[.{ext:\w+}]', $endpoint);
        new Blob\Options($redefine, $this->path);
        new Blob\Get($redefine, $this->path);
    }
}
