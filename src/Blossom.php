<?php

namespace nostriphant\Blossom;

use \nostriphant\Functional\FunctionList;

readonly class Blossom {
    
    public function __construct(private string $path) {
       
    }
    
    static function wrap(string $endpoint, Endpoint\Factory $endpoint_factory) : callable {
        return function(callable $define) use ($endpoint_factory, $endpoint) : void {
            $endpoint_methods = [];
            $endpoint_factory(function(Method $method, callable $handler) use ($define, $endpoint, $endpoint_factory, &$endpoint_methods) {
                $define($method->name, $endpoint, new Authorization(fn(\nostriphant\NIP01\Event $authorization_event) => function(array $attributes, callable $stream) use ($authorization_event, $endpoint_factory, $handler) : array {
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
