<?php

namespace nostriphant\Blossom;

use \nostriphant\Functional\FunctionList;

readonly class Blossom implements \IteratorAggregate {
    
    private \Closure $upload_authorized;
    private Blob\Factory $factory;
    
    public function __construct(private \nostriphant\NIP01\Key $server_key, Blob\Factory $factory, UploadConstraints $constraints) {
        if (isset($constraints->max_upload_size)) {
            $factory = Blob\Factory::recreate($factory, max_file_size: $constraints->max_upload_size);
        }
        $this->factory = $factory;
        
        $unsupported_type_checker = function(string $content_type) use ($constraints) {
            if (isset($constraints->unsupported_content_types) === false) {
                return false;
            } elseif (in_array($content_type, $constraints->unsupported_content_types)) {
                return true;
            }

            foreach (array_filter($constraints->unsupported_content_types, fn(string $unsupported_content_type) => str_ends_with($unsupported_content_type, '/*')) as $unsupported_content_type) {
                list($category, $type) = explode('/', $unsupported_content_type, 2);
                if (str_starts_with($content_type, $category . '/')) {
                    return true;
                }
            }
            return false;
        };
        
        $this->upload_authorized = function(string $pubkey_hex, int $content_length, ?string $content_type, callable $unauthorized) use ($constraints, $unsupported_type_checker) : bool|array {
            if (isset($constraints->allowed_pubkeys)) {
                if (in_array($pubkey_hex, $constraints->allowed_pubkeys) === false) {
                    return $unauthorized(401, 'Pubkey "' . $pubkey_hex . '" is not allowed to upload files');
                }
            }
        
            if (isset($constraints->max_upload_size)) {
                if ($content_length > $constraints->max_upload_size) {
                    return $unauthorized(413, 'File too large. Max allowed size is '.$constraints->max_upload_size.' bytes.');
                }
            }
            
            if (isset($constraints->unsupported_content_types) && isset($content_type)) {
                if ($unsupported_type_checker($content_type)) {
                    return $unauthorized(415, 'Unsupported file type "' . $content_type . '".');
                }
            }
            return true;
        };
    }
    
    static function fromPath(\nostriphant\NIP01\Key $server_key, string $path, UploadConstraints $constraints) : self {
        return new self($server_key, new Blob\Factory($path, null, fn() => true), $constraints);
    }
    
    public function __invoke(UploadConstraints $constraints): self {
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
