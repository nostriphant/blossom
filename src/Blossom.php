<?php

namespace nostriphant\Blossom;

use \nostriphant\Functional\FunctionList;

readonly class Blossom {
    
    public function __construct(private string $path) {
       
    }
    
    static function wrap(string $endpoint_path, Endpoint $endpoint) : callable {
        return function(callable $define) use ($endpoint, $endpoint_path) : void {
            $endpoint_methods = [];
            $endpoint(function(HTTP\Method $method, callable $action_factory) use ($define, $endpoint_path, &$endpoint_methods) {
                $define($method->name, $endpoint_path, new Authorization($action_factory, function(Endpoint\Action $action) : array {
                    $response = $action();
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

            $define('OPTIONS', $endpoint_path, fn(HTTP\ServerRequest $request) => (new Endpoint\Action\Options(...iterator_to_array($endpoint_methods)))());
        };
    }

    public function __invoke() : \Generator {
        yield self::wrap('/upload', new Endpoint\Upload(fn() => new \nostriphant\Blossom\Blob\Uncreated($this->path)));
        yield self::wrap('/{hash:\w+}[.{ext:\w+}]', new Endpoint\Blob(fn(string $hash) => new \nostriphant\Blossom\Blob($this->path . DIRECTORY_SEPARATOR . $hash)));
    }
}
