<?php

namespace nostriphant\Blossom;

use \nostriphant\Functional\FunctionList;

readonly class Blossom implements \IteratorAggregate {
    
    private Blob\Factory $factory;
    
    public function __construct(private \nostriphant\NIP01\Key $server_key, string $data_path, string $server_url, private UploadConstraints $upload_constraints) {
        $files_directory = $data_path . '/files';
        is_dir($files_directory) || mkdir($files_directory);
        $this->factory = new Blob\Factory($files_directory, function(string $hash, ?string $uri = null) use ($server_url) {
            static $urls = [];
            if (isset($uri)) {
                $urls[$hash] = $uri;
            } elseif (isset($urls[$hash]) === false) {
                $urls[$hash] = $server_url . '/' . $hash;
            }
            return $urls[$hash];
        }, $upload_constraints->max_upload_size);
    }
    
    static function fromPath(\nostriphant\NIP01\Key $server_key, string $path, UploadConstraints $constraints) : self {
    }

    #[\Override]
    public function getIterator(): \Traversable {
        $wrap = fn(string $endpoint_path, Endpoint $endpoint) => function(callable $define) use ($endpoint, $endpoint_path) : void {
            
            $additional_headers = new HTTP\AdditionalHeaders;
            $redefine = fn(string $method, callable $request_handler) => $define($method, $endpoint_path, fn(HTTP\ServerRequest $request) => $additional_headers($request_handler($request)));
            
            $endpoint_methods = [];
            $endpoint(function(HTTP\Method $method, Endpoint\Action\Factory $action_factory) use ($redefine, &$endpoint_methods) {
                $redefine($method->name, new Authorization($action_factory));
                $endpoint_methods[] = $method;
            });

            $define('OPTIONS', $endpoint_path, fn(HTTP\ServerRequest $request) => (new Endpoint\Action\Options(...iterator_to_array($endpoint_methods)))());
        };
        
        yield $wrap('/{hash:\w{64}}[.{ext:\w+}]', new Endpoint\Blob($this->factory));
        yield $wrap('/upload', new Endpoint\Upload($this->factory, $this->upload_constraints));
        yield $wrap('/mirror', new Endpoint\Mirror($this->factory, $this->server_key, $this->upload_constraints));
    }
}
