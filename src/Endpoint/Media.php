<?php

namespace nostriphant\Blossom\Endpoint;

readonly class Media implements \nostriphant\Blossom\Endpoint {
    
    private Factory $factory;
    
    public function __construct(\nostriphant\Blossom\Blob\Factory $blob_factory, \nostriphant\Blossom\UploadConstraints $upload_authorized) {
        $this->factory = new Factory(fn(\nostriphant\HTTP\ServerRequest $request) => [$upload_authorized, $blob_factory(null), $request->body]);
    }
    
    #[\Override]
    public function __invoke(callable $define): void {
        $define(\nostriphant\HTTP\Method::HEAD, true, ($this->factory)(Upload\Head::class));
        $define(\nostriphant\HTTP\Method::PUT, true, ($this->factory)(Upload\Put::class));
    }
}
