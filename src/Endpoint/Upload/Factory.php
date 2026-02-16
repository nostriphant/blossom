<?php


namespace nostriphant\Blossom\Endpoint\Upload;

class Factory {
    
    private \Closure $blob_factory;
    
    public function __construct(callable $blob_factory) {
        $this->blob_factory = \Closure::fromCallable($blob_factory);
    }
    
    public function __invoke(string $class) : \nostriphant\Blossom\Endpoint\Action\Factory {
        return new \nostriphant\Blossom\Endpoint\Action\Factory($class, fn(\nostriphant\Blossom\HTTP\ServerRequest $request) => [($this->blob_factory)(), $request]);
    }
}
