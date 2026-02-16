<?php

namespace nostriphant\Blossom\Endpoint;

readonly class Upload implements \nostriphant\Blossom\Endpoint {
    
    private Upload\Factory $factory;
    
    public function __construct(callable $blob_factory) {
        $this->factory = new Upload\Factory($blob_factory);
    }
    
    #[\Override]
    public function __invoke(callable $define): void {
        $define(\nostriphant\Blossom\HTTP\Method::PUT, ($this->factory)(Upload\Put::class));
    }
}
