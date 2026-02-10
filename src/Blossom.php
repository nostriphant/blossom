<?php

namespace nostriphant\Blossom;

use \nostriphant\Functional\FunctionList;

readonly class Blossom {
    
    public function __construct(private Blob\Factory $blob_factory) {
       
    }

    public function __invoke(FunctionList $routes) : FunctionList {
        
        return $routes
            ->bind(new Endpoint\Blob($this->blob_factory));
    }
}
