<?php

namespace nostriphant\Blossom\Endpoint;

readonly class Blob implements \nostriphant\Blossom\Endpoint {
    
    private Blob\Factory $factory;
    
    public function __construct(string $path) {
        $this->factory = new Blob\Factory(fn(string $hash) => new \nostriphant\Blossom\Blob($path . DIRECTORY_SEPARATOR . $hash));
    }
    
    
    #[\Override]
    public function __invoke(callable $define) : void {
        $define(\nostriphant\Blossom\HTTP\Method::HEAD, ($this->factory)(Blob\Get::class));
        $define(\nostriphant\Blossom\HTTP\Method::GET, ($this->factory)(Blob\Get::class));
        $define(\nostriphant\Blossom\HTTP\Method::DELETE, ($this->factory)(Blob\Delete::class));
    }
}
