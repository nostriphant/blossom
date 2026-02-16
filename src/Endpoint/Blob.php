<?php

namespace nostriphant\Blossom\Endpoint;

readonly class Blob implements \nostriphant\Blossom\Endpoint {
    
    
    public function __construct(private string $path) {
    }
    
    
    #[\Override]
    public function __invoke(callable $define) : void {
        
        $factory_factory = new Blob\Factory(fn(string $hash) => new \nostriphant\Blossom\Blob($this->path . DIRECTORY_SEPARATOR . $hash));
        
        $define(\nostriphant\Blossom\HTTP\Method::HEAD, $factory_factory(Blob\Get::class));
        $define(\nostriphant\Blossom\HTTP\Method::GET, $factory_factory(Blob\Get::class));
        $define(\nostriphant\Blossom\HTTP\Method::DELETE, $factory_factory(Blob\Delete::class));
    }
}
