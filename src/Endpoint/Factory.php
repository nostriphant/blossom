<?php

namespace nostriphant\Blossom\Endpoint;

interface Factory {
    public function __invoke(callable $define) : void;
}
