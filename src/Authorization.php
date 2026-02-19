<?php

namespace nostriphant\Blossom;

class Authorization {
    private \Closure $action_factory;
    private \Closure $handler;
    
    public function __construct(callable $action_factory, callable $handler) {
        $this->action_factory = \Closure::fromCallable($action_factory);
        $this->handler = \Closure::fromCallable($handler);
    }
    
    public function __invoke(HTTP\ServerRequest $request) : array {
        $unauthorized = fn(int $status, string $reason) => ['status' => $status, 'headers' => ['x-reason' => $reason]];
        if (isset($request->authorization) === false) {
            return $unauthorized(401, 'No authorization found');
        } elseif (\nostriphant\NIP01\Event::verify($request->authorization) === false) {
            return $unauthorized(401, '');
        } elseif ($request->authorization->kind !== 24242) {
            return $unauthorized(401, '');
        } elseif (empty($request->authorization->content)) {
            return $unauthorized(401, '');
        } elseif (\nostriphant\NIP01\Event::hasTag($request->authorization, 't') === false) {
            return $unauthorized(401, '');
        } elseif (\nostriphant\NIP01\Event::hasTag($request->authorization, 'expiration') === false) {
            return $unauthorized(401, '');
        } elseif (\nostriphant\NIP01\Event::extractTagValues($request->authorization, 'expiration')[0][0] < time()) {
            return $unauthorized(401, '');
        }
        
        $additional_headers = [];
        $offset = strlen('HTTP_');
        foreach ($request->headers as $header => $value) {
            $additional_headers[substr($header, $offset)] = $value;
        }
        
        $action = ($this->action_factory)($request);
        return $action->authorize($request->authorization, $additional_headers, fn() => ($this->handler)($action($request->authorization->pubkey)), $unauthorized);
    }
}
