<?php

namespace nostriphant\Blossom\Endpoint;

readonly class Blob implements Factory {
    
    
    public function __construct(private string $path) {
    }
    
    
    #[\Override]
    public function __invoke(callable $define) : void {
        $define(\nostriphant\Blossom\Method::HEAD, fn(\nostriphant\Blossom\ServerRequest $request) => new Blob\Get(new \nostriphant\Blossom\Blob($this->path . DIRECTORY_SEPARATOR . $request->attributes['hash'])));
        $define(\nostriphant\Blossom\Method::GET, fn(\nostriphant\Blossom\ServerRequest $request) => new Blob\Get(new \nostriphant\Blossom\Blob($this->path . DIRECTORY_SEPARATOR . $request->attributes['hash'])));
        $define(\nostriphant\Blossom\Method::DELETE, fn(\nostriphant\Blossom\ServerRequest $request) => new Blob\Delete(new \nostriphant\Blossom\Blob($this->path . DIRECTORY_SEPARATOR . $request->attributes['hash'])));
    }
}
