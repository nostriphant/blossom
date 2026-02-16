<?php

namespace nostriphant\Blossom\Endpoint;

readonly class Blob implements \nostriphant\Blossom\Endpoint {
    private Blob\Factory $factory;
    
    public function __construct(callable $blob_factory) {
        $this->factory = new Blob\Factory($blob_factory);
    }
    
    
    #[\Override]
    public function __invoke(callable $define) : void {
        $define(\nostriphant\Blossom\HTTP\Method::HEAD, ($this->factory)(Blob\Get::class));
        $define(\nostriphant\Blossom\HTTP\Method::GET, ($this->factory)(Blob\Get::class));
        $define(\nostriphant\Blossom\HTTP\Method::DELETE, ($this->factory)(Blob\Delete::class));
    }
}
