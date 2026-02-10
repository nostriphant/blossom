<?php

namespace nostriphant\Blossom\Endpoint;

use nostriphant\Blossom\Endpoint;

readonly class Blob implements Endpoint {
    
    public function __construct(private string $path) {

    }
    
    private function blob(callable $exists) : callable {
        return fn(array $attributes) => (new \nostriphant\Blossom\Blob($this->path . DIRECTORY_SEPARATOR . $attributes['hash'], $exists, fn() => ['status' => 404]))();
    }
    
    #[\Override]
    public function __invoke(callable $define) : void {
        $define('OPTIONS', '/{hash:\w+}[.{ext:\w+}]', $this->blob(new Blob\Options()));
        $define('GET', '/{hash:\w+}[.{ext:\w+}]', $this->blob(new Blob\Get()));
    }
}
