<?php

namespace nostriphant\Blossom;

use \nostriphant\Functional\FunctionList;

readonly class Blossom {
    
    public function __construct(private string $path) {
       
    }

    public function __invoke(FunctionList $routes) : FunctionList {
        
        $blob_factory = fn(string $hash, callable $exists) => (new \nostriphant\Blossom\Blob($this->path . DIRECTORY_SEPARATOR . $hash, $exists, fn() => ['status' => 404]))();
        
        return $routes
            ->bind(new Endpoint\Blob($blob_factory));
    }
}
