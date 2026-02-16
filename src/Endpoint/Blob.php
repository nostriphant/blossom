<?php

namespace nostriphant\Blossom\Endpoint;

readonly class Blob implements \nostriphant\Blossom\Endpoint {
    
    
    public function __construct(private string $path) {
    }
    
    
    #[\Override]
    public function __invoke(callable $define) : void {
        $define(\nostriphant\Blossom\HTTP\Method::HEAD, fn(\nostriphant\Blossom\HTTP\ServerRequest $request) => new Blob\Get(new \nostriphant\Blossom\Blob($this->path . DIRECTORY_SEPARATOR . $request->attributes['hash'])));
        $define(\nostriphant\Blossom\HTTP\Method::GET, fn(\nostriphant\Blossom\HTTP\ServerRequest $request) => new Blob\Get(new \nostriphant\Blossom\Blob($this->path . DIRECTORY_SEPARATOR . $request->attributes['hash'])));
        $define(\nostriphant\Blossom\HTTP\Method::DELETE, fn(\nostriphant\Blossom\HTTP\ServerRequest $request) => new Blob\Delete(new \nostriphant\Blossom\Blob($this->path . DIRECTORY_SEPARATOR . $request->attributes['hash'])));
    }
}
