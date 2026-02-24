<?php

namespace nostriphant\Blossom;

use \nostriphant\Functional\FunctionList;

readonly class Blossom implements \IteratorAggregate {
    
    private Blob\Factory $factory;
    
    public function __construct(private \nostriphant\NIP01\Key $server_key, Blob\Factory $factory, private UploadConstraints $upload_constraints) {
        if (isset($upload_constraints->max_upload_size)) {
            $factory = Blob\Factory::recreate($factory, max_file_size: $upload_constraints->max_upload_size);
        }
        $this->factory = $factory;
    }
    
    static function fromPath(\nostriphant\NIP01\Key $server_key, string $path, UploadConstraints $constraints) : self {
        return new self($server_key, new Blob\Factory($path, null, fn() => true), $constraints);
    }

    #[\Override]
    public function getIterator(): \Traversable {
        $wrap = fn(string $endpoint_path, Endpoint $endpoint) => function(callable $define) use ($endpoint, $endpoint_path) : void {
            $endpoint_methods = [];
            $endpoint(function(HTTP\Method $method, Endpoint\Action\Factory $action_factory) use ($define, $endpoint_path, &$endpoint_methods) {
                $define($method->name, $endpoint_path, new Authorization($action_factory, new HTTP\AdditionalHeaders));
                $endpoint_methods[] = $method;
            });

            $define('OPTIONS', $endpoint_path, fn(HTTP\ServerRequest $request) => (new Endpoint\Action\Options(...iterator_to_array($endpoint_methods)))());
        };
        
        yield $wrap('/{hash:\w{64}}[.{ext:\w+}]', new Endpoint\Blob($this->factory));
        yield $wrap('/upload', new Endpoint\Upload($this->factory, $this->upload_constraints));
        yield $wrap('/mirror', new Endpoint\Mirror($this->factory, $this->server_key, $this->upload_constraints));
    }
}
