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
        if (\nostriphant\NIP01\Event::verify($authorization_event) === false) {
            return $unauthorized;
        } elseif ($authorization_event->kind !== 24242) {
            return $unauthorized;
        } elseif (empty($authorization_event->content)) {
            return $unauthorized;
        } elseif (\nostriphant\NIP01\Event::hasTag($authorization_event, 't') === false) {
            return $unauthorized;
        } elseif (\nostriphant\NIP01\Event::hasTag($authorization_event, 'expiration') === false) {
            return $unauthorized;
        } elseif (\nostriphant\NIP01\Event::extractTagValues($authorization_event, 'expiration')[0][0] < time()) {
            return $unauthorized;
        }
        
        return $handler($authorization_event);
    }
    
    static function authorization_middelware(Method $method, string $endpoint, callable $handler) : array {
        return [$method->name, $endpoint, function(?string $authorization = null) use ($method, $handler) : callable {
            return match($method) {
                default => self::authorize($authorization, $handler)
            };
        }];
    }
    
    static function wrap(string $endpoint, Endpoint\Factory $endpoint_factory) : callable {
        return function(callable $define) use ($endpoint_factory, $endpoint) : void {
            $endpoint_methods = [];
            $endpoint_factory(function(Method $method, callable $handler) use ($define, $endpoint, $endpoint_factory, &$endpoint_methods) {
                $define(...self::authorization_middelware($method, $endpoint, fn(\nostriphant\NIP01\Event $authorization_event) => function(array $attributes, callable $stream) use ($authorization_event, $endpoint_factory, $handler) : array {
                    $response = $handler(...$endpoint_factory->attributes($attributes, $stream))($authorization_event);

                    $additional_headers = ['Access-Control-Allow-Origin' => '*'];
                    if (isset($response['body']) === false) {
                    } elseif(isset($headers['Content-Length']) === false) {
                        $additional_headers['Content-Length'] = strlen($response['body']);
                    }

                    $response['headers'] = array_merge($additional_headers, $response['headers'] ?? []);

                    return $response;
                }));
                $endpoint_methods[] = $method;
            });

            $define('OPTIONS', $endpoint, fn(?string $authorization = null) => fn(array $attributes, callable $stream) => (new Endpoint\Options(...iterator_to_array($endpoint_methods)))());
        };
    }

    public function __invoke() : \Generator {
        yield self::wrap('/upload', new Endpoint\Upload($this->path));
        yield self::wrap('/{hash:\w+}[.{ext:\w+}]', new Endpoint\Blob($this->path));
    }
}
