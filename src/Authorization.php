<?php

namespace nostriphant\Blossom;

class Authorization {
    private \Closure $handler;
    private array $unauthorized = ['status' => 401];
    
    public function __construct(callable $handler) {
        $this->handler = \Closure::fromCallable($handler);
    }
    
    public function __invoke(HTTP\ServerRequest $request) : array {
        $authorization = $request->headers['HTTP_AUTHORIZATION'] ?? null;
        
        if (isset($authorization) === false) {
            return $this->unauthorized;
        }
        
        list($type, $base64) = explode(' ', trim($authorization));
        if (strcasecmp($type, 'nostr') !== 0) {
            return $this->unauthorized;
        }
        
        $authorization_event = new \nostriphant\NIP01\Event(...\nostriphant\NIP01\Nostr::decode(base64_decode($base64)));
        if (\nostriphant\NIP01\Event::verify($authorization_event) === false) {
            return $this->unauthorized;
        } elseif ($authorization_event->kind !== 24242) {
            return $this->unauthorized;
        } elseif (empty($authorization_event->content)) {
            return $this->unauthorized;
        } elseif (\nostriphant\NIP01\Event::hasTag($authorization_event, 't') === false) {
            return $this->unauthorized;
        } elseif (\nostriphant\NIP01\Event::hasTag($authorization_event, 'expiration') === false) {
            return $this->unauthorized;
        } elseif (\nostriphant\NIP01\Event::extractTagValues($authorization_event, 'expiration')[0][0] < time()) {
            return $this->unauthorized;
        }
        
        return ($this->handler)($authorization_event);
    }
}
