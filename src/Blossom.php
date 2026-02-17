<?php

namespace nostriphant\Blossom;

use \nostriphant\Functional\FunctionList;

readonly class Blossom implements \IteratorAggregate {
    
    private \Closure $upload_authorized;
    
    public function __construct(private \nostriphant\NIP01\Key $server_key, private Blob\Factory $factory, callable $upload_authorized) {
        $this->upload_authorized = \Closure::fromCallable($upload_authorized);
    }
    
    static function fromPath(\nostriphant\NIP01\Key $server_key, string $path) : self {
        return new self($server_key, new Blob\Factory($path), fn(string $pubkey_hex) => true);
    }
    
    public function __invoke(callable $upload_authorized): self {
        return new self($this->server_key, $this->factory, $upload_authorized);
    }

    #[\Override]
    public function getIterator(): \Traversable {
        $wrap = fn(string $endpoint_path, Endpoint $endpoint) => function(callable $define) use ($endpoint, $endpoint_path) : void {
            $endpoint_methods = [];
            $endpoint(function(HTTP\Method $method, callable $action_factory) use ($define, $endpoint_path, &$endpoint_methods) {
                $define($method->name, $endpoint_path, new Authorization($action_factory, new HTTP\AdditionalHeaders));
                $endpoint_methods[] = $method;
            });

            $define('OPTIONS', $endpoint_path, fn(HTTP\ServerRequest $request) => (new Endpoint\Action\Options(...iterator_to_array($endpoint_methods)))());
        };
        
        yield $wrap('/{hash:\w{64}}[.{ext:\w+}]', new Endpoint\Blob($this->factory));
        yield $wrap('/upload', new Endpoint\Upload($this->factory, $this->upload_authorized));
        yield $wrap('/mirror', new Endpoint\Mirror($this->factory, $this->server_key, $this->upload_authorized));
    }
}
