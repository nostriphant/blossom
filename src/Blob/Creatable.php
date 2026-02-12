<?php

namespace nostriphant\Blossom\Blob;

interface Creatable {
    function __invoke(callable $stream): \nostriphant\Blossom\Blob;
}
