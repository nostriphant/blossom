<?php

namespace nostriphant\Blossom;

class Authorization {
    
    public function __construct(private Endpoint\Action\Factory $action_factory) {
    }
    
    public function __invoke(HTTP\ServerRequest $request) : array {
        $unauthorized = fn(int $status, string $reason) => ['status' => $status, 'headers' => ['x-reason' => $reason]];
        if (isset($request->authorization) === false) {
            return $unauthorized(401, 'No authorization found');
        } elseif (\nostriphant\NIP01\Event::verify($request->authorization) === false) {
            return $unauthorized(401, 'Invalid authorization event');
        } elseif ($request->authorization->kind !== 24242) {
            return $unauthorized(401, 'Incorrent authorization event kind');
        } elseif (empty($request->authorization->content)) {
            return $unauthorized(401, 'Authorization event content is missing');
        } elseif (\nostriphant\NIP01\Event::hasTag($request->authorization, 't') === false) {
            return $unauthorized(401, 'Authorization event is missing t-tag');
        } elseif (\nostriphant\NIP01\Event::hasTag($request->authorization, 'expiration') === false) {
            return $unauthorized(401, 'Authorization event is missing expiration-tag');
        } elseif (\nostriphant\NIP01\Event::extractTagValues($request->authorization, 'expiration')[0][0] < time()) {
            return $unauthorized(401, 'Authorization event has expired');
        }
        
        $additional_headers = [];
        $offset = strlen('HTTP_');
        foreach ($request->headers as $header => $value) {
            $additional_headers[substr($header, $offset)] = $value;
        }
        
        $action = ($this->action_factory)($request);
        return $action($request->authorization, $additional_headers, fn(callable $action) => $action($request->authorization->pubkey), $unauthorized);
    }
    
    static function makeEvent(\nostriphant\NIP01\Key $key, string $action, string $hash, string $description) : \nostriphant\NIP01\Event {
        $mirror_authorization_rumor = new \nostriphant\NIP01\Rumor(time(), $key(\nostriphant\NIP01\Key::public()), 24242,  $description, [
            ['t', $action],
            ["expiration", time() + 3600],
            ['x', $hash]
        ]);
        return $mirror_authorization_rumor($key);
    }
}
