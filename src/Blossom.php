<?php

namespace nostriphant\Blossom;

use \nostriphant\Functional\FunctionList;

readonly class Blossom implements \IteratorAggregate {
    
    private \Closure $upload_authorized;
    
    public function __construct(private Blob\Factory $factory, callable $upload_authorized) {
        $this->upload_authorized = \Closure::fromCallable($upload_authorized);
    }
    
    static function fromPath(string $path) : self {
        return new self(new Blob\Factory($path), fn(string $pubkey_hex) => true);
    }
    
    public function __invoke(callable $upload_authorized): self {
        return new self($this->factory, $upload_authorized);
    }

    #[\Override]
    public function getIterator(): \Traversable {
        $wrap = fn(string $endpoint_path, Endpoint $endpoint) => function(callable $define) use ($endpoint, $endpoint_path) : void {
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
        
        yield $wrap('/upload', new Endpoint\Upload($this->factory, $this->upload_authorized));
        yield $wrap('/{hash:\w+}[.{ext:\w+}]', new Endpoint\Blob($this->factory));
    }
}
