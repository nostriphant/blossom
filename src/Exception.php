<?php

namespace nostriphant\Blossom;


class Exception extends \Exception {
    public function __construct(int $code, string $reason) {
        parent::__construct($reason, $code);
    }
}
