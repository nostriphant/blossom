<?php

namespace nostriphant\Blossom;

use \nostriphant\Functional\FunctionList;

readonly class Blossom {
    
    public function __construct(private Blob\Factory $blob_factory) {
       
    }

    public function __invoke() : \Generator {
        yield new Endpoint\Upload($this->blob_factory);
        yield new Endpoint\Blob($this->blob_factory);
    }
}
