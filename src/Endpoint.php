<?php

namespace nostriphant\Blossom;

interface Endpoint {
    public function __invoke(callable $define) : void;
}
