<?php

namespace nostriphant\Blossom\Endpoint;

readonly class Upload implements Factory {
    
    
    public function __construct(private string $path) {

    }
    
    #[\Override]
    public function __invoke(callable $define): void {
        $define(\nostriphant\Blossom\Method::PUT, fn(\nostriphant\Blossom\ServerRequest $request) => new Upload\Put(new \nostriphant\Blossom\Blob\Uncreated($this->path), $request));
    }
}
