<?php

namespace nostriphant\Blossom;

use \nostriphant\Functional\FunctionList;

readonly class Blossom {
    
    public function __construct(private string $path) {
       
    }

    public function __invoke(FunctionList $routes) : FunctionList {
        return $routes
            ->bind(new Endpoint\Blob\Options($this->path))
            ->bind(new Endpoint\Blob\Get($this->path));
    }
}
