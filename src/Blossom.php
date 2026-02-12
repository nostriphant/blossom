<?php

namespace nostriphant\Blossom;

use \nostriphant\Functional\FunctionList;

readonly class Blossom {
    
    public function __construct(private string $path) {
       
    }

    public function __invoke() : \Generator {
        yield new Endpoint\Upload($this->path);
        yield new Endpoint\Blob($this->path);
    }
}
