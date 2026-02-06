<?php

namespace nostriphant\Blossom\Endpoint;

use nostriphant\Blossom\Endpoint;

readonly class Blob implements Endpoint {
    
    public function __construct(private string $path) {

    }
    
    private function blob(string $hash) : \nostriphant\Blossom\Blob {
        return new \nostriphant\Blossom\Blob($this->path . DIRECTORY_SEPARATOR . $hash, fn() => ['status' => 404]);
    }
    
    #[\Override]
    public function __invoke(callable $define) : void {
        $define('OPTIONS', '/{hash:\w+}[.{ext:\w+}]', fn(array $attributes) => $this->blob($attributes['hash'])(new Blob\Options()));
        $define('GET', '/{hash:\w+}[.{ext:\w+}]', fn(array $attributes) => $this->blob($attributes['hash'])(new Blob\Get()));
    }
}
