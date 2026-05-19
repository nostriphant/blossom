<?php

namespace nostriphant\Blossom\Endpoint;

readonly class Blob implements \nostriphant\Blossom\Endpoint {
    private Factory $factory;
    
    public function __construct(\nostriphant\Blossom\Blob\Factory $blob_factory) {
        $this->factory = new Factory(fn(\nostriphant\HTTP\ServerRequest $request) => [$blob_factory($request->attributes['hash'])]);
    }
    
    
    #[\Override]
    public function __invoke(callable $define) : void {
        $define(\nostriphant\HTTP\Method::HEAD, false, ($this->factory)(Blob\Get::class));
        $define(\nostriphant\HTTP\Method::GET, false, ($this->factory)(Blob\Get::class));
        $define(\nostriphant\HTTP\Method::DELETE, true, ($this->factory)(Blob\Delete::class));
    }
}
