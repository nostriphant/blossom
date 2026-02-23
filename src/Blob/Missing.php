<?php

namespace nostriphant\Blossom\Blob;

readonly class Missing extends \nostriphant\Blossom\Blob {
    public function __invoke(): array {
        return ['status' => 404];
    }
}
