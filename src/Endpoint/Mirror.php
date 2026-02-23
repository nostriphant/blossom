<?php

namespace nostriphant\Blossom\Endpoint;

readonly class Mirror implements \nostriphant\Blossom\Endpoint {
    
    private Factory $factory;
    
    public function __construct(\nostriphant\Blossom\Blob\Factory $blob_factory, \nostriphant\NIP01\Key $server_key, callable $upload_authorized) {
        $this->factory = new Factory(fn(\nostriphant\Blossom\HTTP\ServerRequest $request) => [$upload_authorized, $blob_factory(null), $server_key, $request]);
    }
    
    #[\Override]
    public function __invoke(callable $define): void {
        $define(\nostriphant\Blossom\HTTP\Method::PUT, ($this->factory)(Mirror\Put::class));
    }
}
