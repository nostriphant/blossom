<?php

namespace nostriphant\Blossom\Blob;

readonly class Failed extends \nostriphant\Blossom\Blob {

    public function __construct(private int $code, private string $reason) {
        $this->exists = false;
    }

    public function __invoke(): array {
        return ['status' => $this->code, 'headers' => ['x-reason' => $this->reason]];
    }
}
