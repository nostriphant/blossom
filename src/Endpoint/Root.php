<?php

namespace nostriphant\Blossom\Endpoint;

readonly class Root implements \nostriphant\Blossom\Endpoint {
    private Factory $factory;
    
    public function __construct() {
        $this->factory = new Factory(fn(\nostriphant\Blossom\HTTP\ServerRequest $request) => []);
    }
    
    
    #[\Override]
    public function __invoke(callable $define) : void {
        $define(\nostriphant\Blossom\HTTP\Method::HEAD, false, ($this->factory)(Root\Get::class));
        $define(\nostriphant\Blossom\HTTP\Method::GET, false, ($this->factory)(Root\Get::class));
    }
}
