<?php

namespace nostriphant\Blossom\Endpoint;

use nostriphant\Blossom\Endpoint;

readonly class Blob implements Endpoint {
    
    public function __construct(private string $path) {

    }
    
    #[\Override]
    public function __invoke(callable $define) : void {
        $define('OPTIONS', '/{hash:\w+}[.{ext:\w+}]', fn(array $attributes) => (new Blob\Options(new \nostriphant\Blossom\Blob($this->path . DIRECTORY_SEPARATOR . $attributes['hash'])))());
        
        $define('GET', '/{hash:\w+}[.{ext:\w+}]', fn(array $attributes) => (new Blob\Get(new \nostriphant\Blossom\Blob($this->path . DIRECTORY_SEPARATOR . $attributes['hash'])))());
    }
}
