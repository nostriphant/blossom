<?php

namespace nostriphant\Blossom\Endpoint\Action;

class Factory {
    private \Closure $translate_request;
    public function __construct(private string $class, callable $translate_request) {
        $this->translate_request = \Closure::fromCallable($translate_request);
    }

    public function __invoke(\nostriphant\Blossom\HTTP\ServerRequest $request) : \nostriphant\Blossom\Endpoint\Action {
        return new ($this->class)(...call_user_func($this->translate_request, $request));
    }
}
