<?php

namespace nostriphant\Blossom\Endpoint;

readonly class Blob implements \nostriphant\Blossom\Endpoint {
    
    
    public function __construct(private string $path) {
    }
    
    
    #[\Override]
    public function __invoke(callable $define) : void {
        
        $factory_factory = fn($class) => new class($class, fn(string $hash) => new \nostriphant\Blossom\Blob($this->path . DIRECTORY_SEPARATOR . $hash)) implements Action\Factory {
            private \Closure $blob_factory;
            public function __construct(private string $class, callable $blob_factory) {
                $this->blob_factory = \Closure::fromCallable($blob_factory);
            }
            
            public function __invoke(\nostriphant\Blossom\HTTP\ServerRequest $request) : \nostriphant\Blossom\Endpoint\Action {
                return new ($this->class)(($this->blob_factory)($request->attributes['hash']));
            }
        };
        
        $define(\nostriphant\Blossom\HTTP\Method::HEAD, $factory_factory(Blob\Get::class));
        $define(\nostriphant\Blossom\HTTP\Method::GET, $factory_factory(Blob\Get::class));
        $define(\nostriphant\Blossom\HTTP\Method::DELETE, $factory_factory(Blob\Delete::class));
    }
}
