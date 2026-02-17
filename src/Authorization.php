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
        $authorization = $request->headers['HTTP_AUTHORIZATION'] ?? null;
        
        if (isset($authorization) === false) {
            return $unauthorized(401, '');
        }
        
        list($type, $base64) = explode(' ', trim($authorization));
        if (strcasecmp($type, 'nostr') !== 0) {
            return $unauthorized(401, '');
        }
        
        $authorization_event = new \nostriphant\NIP01\Event(...\nostriphant\NIP01\Nostr::decode(base64_decode($base64)));
        if (\nostriphant\NIP01\Event::verify($authorization_event) === false) {
            return $unauthorized(401, '');
        } elseif ($authorization_event->kind !== 24242) {
            return $unauthorized(401, '');
        } elseif (empty($authorization_event->content)) {
            return $unauthorized(401, '');
        } elseif (\nostriphant\NIP01\Event::hasTag($authorization_event, 't') === false) {
            return $unauthorized(401, '');
        } elseif (\nostriphant\NIP01\Event::hasTag($authorization_event, 'expiration') === false) {
            return $unauthorized(401, '');
        } elseif (\nostriphant\NIP01\Event::extractTagValues($authorization_event, 'expiration')[0][0] < time()) {
            return $unauthorized(401, '');
        }
        
        $additional_headers = [];
        $offset = strlen('HTTP_');
        foreach ($request->headers as $header => $value) {
            $additional_headers[substr($header, $offset)] = $value;
        }
        
        $action = ($this->action_factory)($request);
        return $action->authorize($authorization_event, $additional_headers, fn() => ($this->handler)($action($authorization_event->pubkey)), $unauthorized);
    }
}
