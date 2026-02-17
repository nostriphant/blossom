<?php

namespace nostriphant\Blossom\Endpoint;

readonly class Upload implements \nostriphant\Blossom\Endpoint {
    
    private Factory $factory;
    
    public function __construct(\nostriphant\Blossom\Blob\Factory $blob_factory, callable $upload_authorized) {
        $this->factory = new Factory(fn(\nostriphant\Blossom\HTTP\ServerRequest $request) => [$upload_authorized, $blob_factory(), $request]);
    }
    
    #[\Override]
    public function __invoke(callable $define): void {
        $define(\nostriphant\Blossom\HTTP\Method::HEAD, ($this->factory)(Upload\Head::class));
        $define(\nostriphant\Blossom\HTTP\Method::PUT, ($this->factory)(Upload\Put::class));
    }
}
