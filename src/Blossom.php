<?php

namespace nostriphant\Blossom;

use \nostriphant\Functional\FunctionList;

readonly class Blossom {
    
    public function __construct(private string $path) {
       
    }
    
    static function authorize(?string $authorization, callable $handler) {
        $unauthorized = fn(array $attributes, callable $stream) => ['status' => 401];
        
        if (isset($authorization) === false) {
            return $unauthorized;
        }
        
        list($type, $base64) = explode(' ', trim($authorization));
        if (strcasecmp($type, 'nostr') !== 0) {
            return $unauthorized;
        }
        
        $authorization_event = new \nostriphant\NIP01\Event(...\nostriphant\NIP01\Nostr::decode(base64_decode($base64)));
        if ($authorization_event->kind !== 24242) {
            return $unauthorized;
        } elseif (empty($authorization_event->content)) {
            return $unauthorized;
        } elseif (\nostriphant\NIP01\Event::hasTag($authorization_event, 't') === false) {
            return $unauthorized;
        } elseif (\nostriphant\NIP01\Event::hasTag($authorization_event, 'expiration') === false) {
            return $unauthorized;
        }
        
        return $handler($authorization_event);
    }
    
    static function authorization_middelware(Method $method, string $endpoint, callable $handler) : array {
        return [$method->name, $endpoint, function(?string $authorization = null) use ($method, $handler) : callable {
            return match($method) {
                Method::OPTIONS => $handler,
                default => self::authorize($authorization, $handler)
            };
        }];
    }
    
    static function wrap(string $endpoint, Endpoint\Factory $endpoint_factory, array &$endpoint_methods) : callable {
        $endpoint_methods[$endpoint] = [];
        return function(callable $define) use ($endpoint_factory, $endpoint, &$endpoint_methods) {
            return $endpoint_factory(function(Method $method, callable $handler) use ($define, $endpoint, $endpoint_factory, &$endpoint_methods) {
                $endpoint_methods[$endpoint][] = $method;
                return $define(...self::authorization_middelware($method, $endpoint, fn(\nostriphant\NIP01\Event $authorization_event) => function(array $attributes, callable $stream) use ($authorization_event, $endpoint_factory, $handler) : array {
                    $response = $handler(...$endpoint_factory->attributes($attributes, $stream))($authorization_event);

                    $additional_headers = ['Access-Control-Allow-Origin' => '*'];
                    if (isset($response['body']) === false) {
                    } elseif(isset($headers['Content-Length']) === false) {
                        $additional_headers['Content-Length'] = strlen($response['body']);
                    }

                    $response['headers'] = array_merge($additional_headers, $response['headers'] ?? []);

                    return $response;
                }));
                
            });
        };
    }

    public function __invoke() : \Generator {
        $endpoint_methods = [];
        yield self::wrap('/upload', new Endpoint\Upload($this->path), $endpoint_methods);
        yield self::wrap('/{hash:\w+}[.{ext:\w+}]', new Endpoint\Blob($this->path), $endpoint_methods);
        foreach ($endpoint_methods as $endpoint => $methods) {
            yield fn(callable $define) => $define(...self::authorization_middelware(Method::OPTIONS, $endpoint, fn(array $attributes, callable $stream) => (new Endpoint\Options(...$methods))()));
        }
    }
}
