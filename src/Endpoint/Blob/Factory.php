<?php


namespace nostriphant\Blossom\Endpoint\Blob;

class Factory {
    
    private \Closure $blob_factory;
    
    public function __construct(callable $blob_factory) {
        $this->blob_factory = \Closure::fromCallable($blob_factory);
    }
    
    public function __invoke(string $class) : \nostriphant\Blossom\Endpoint\Action\Factory {
        return new class($class, fn(\nostriphant\Blossom\HTTP\ServerRequest $request) => [($this->blob_factory)($request->attributes['hash'])]) implements \nostriphant\Blossom\Endpoint\Action\Factory  {
            private \Closure $translate_request;
            public function __construct(private string $class, callable $translate_request) {
                $this->translate_request = \Closure::fromCallable($translate_request);
            }

            public function __invoke(\nostriphant\Blossom\HTTP\ServerRequest $request) : \nostriphant\Blossom\Endpoint\Action {
                return new ($this->class)(...call_user_func($this->translate_request, $request));
            }
        };
    }
}
