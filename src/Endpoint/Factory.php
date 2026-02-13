<?php

namespace nostriphant\Blossom\Endpoint;

interface Factory {
    public function attributes(array $attributes, callable $stream) : array;
    public function __invoke(callable $define) : void;
}
