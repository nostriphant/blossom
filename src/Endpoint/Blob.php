<?php

namespace nostriphant\Blossom\Endpoint;

use nostriphant\Blossom\Endpoint;

readonly class Blob implements Endpoint {
    
    public function __construct(private string $path) {

    }
    
    private function blob(string $hash) : \nostriphant\Blossom\Blob {
        return new \nostriphant\Blossom\Blob($this->path . DIRECTORY_SEPARATOR . $hash);
    }
    
    #[\Override]
    public function __invoke(callable $define) : void {
        $define('OPTIONS', '/{hash:\w+}[.{ext:\w+}]', fn(array $attributes) => (new Blob\Options($this->blob($attributes['hash'])))());
        
        $define('GET', '/{hash:\w+}[.{ext:\w+}]', fn(array $attributes) => (new Blob\Get($this->blob($attributes['hash'])))());
    }
}
