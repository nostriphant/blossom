<?php

namespace nostriphant\Blossom\Endpoint;

readonly class Blob implements \nostriphant\Blossom\Endpoint {
    private Factory $factory;
    
    public function __construct(\nostriphant\Blossom\Blob\Factory $blob_factory) {
        $this->factory = new Factory(fn(\nostriphant\Blossom\HTTP\ServerRequest $request) => [$blob_factory($request->attributes['hash'])]);
    }
    
    
    #[\Override]
    public function __invoke(callable $define) : void {
        $define(\nostriphant\Blossom\HTTP\Method::HEAD, ($this->factory)(Blob\Get::class));
        $define(\nostriphant\Blossom\HTTP\Method::GET, ($this->factory)(Blob\Get::class));
        $define(\nostriphant\Blossom\HTTP\Method::DELETE, ($this->factory)(Blob\Delete::class));
    }
}
