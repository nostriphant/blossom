<?php

namespace nostriphant\Blossom;

use \nostriphant\Functional\FunctionList;

readonly class Blossom {
    
    public function __construct(private string $path) {
       
    }

    public function __invoke(FunctionList $routes) : FunctionList {
        return $routes
            ->bind(new Endpoint\Blob('HEAD', $this->path))
            ->bind(new Endpoint\Blob('GET', $this->path));
    }
}
