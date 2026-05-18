<?php

namespace nostriphant\Blossom\Endpoint;

readonly class Blobs implements \nostriphant\Blossom\Endpoint {
    private Factory $factory;
    
    public function __construct(\nostriphant\Blossom\Blobs\Factory $list_factory) {
        $this->factory = new Factory(fn(\nostriphant\Blossom\HTTP\ServerRequest $request) => [$list_factory($request->attributes['pubkey'], $request->attributes['limit'] ?? null, $request->attributes['cursor'] ?? null)]);
    }
    
    
    #[\Override]
    public function __invoke(callable $define) : void {
        $define(\nostriphant\Blossom\HTTP\Method::HEAD, false, ($this->factory)(Blobs\Get::class));
        $define(\nostriphant\Blossom\HTTP\Method::GET, false, ($this->factory)(Blobs\Get::class));
    }
}
