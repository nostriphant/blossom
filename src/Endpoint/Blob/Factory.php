<?php


namespace nostriphant\Blossom\Endpoint\Blob;

class Factory {
    
    private \Closure $blob_factory;
    
    public function __construct(callable $blob_factory) {
        $this->blob_factory = \Closure::fromCallable($blob_factory);
    }
    
    public function __invoke(string $class) : \nostriphant\Blossom\Endpoint\Action\Factory {
        return new class($class, $this->blob_factory) implements \nostriphant\Blossom\Endpoint\Action\Factory  {
            private \Closure $blob_factory;
            public function __construct(private string $class, callable $blob_factory) {
                $this->blob_factory = \Closure::fromCallable($blob_factory);
            }

            public function __invoke(\nostriphant\Blossom\HTTP\ServerRequest $request) : \nostriphant\Blossom\Endpoint\Action {
                return new ($this->class)(($this->blob_factory)($request->attributes['hash']));
            }
        };
    }
}
