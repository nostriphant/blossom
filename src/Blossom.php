<?php

namespace nostriphant\Blossom;

use \nostriphant\Functional\FunctionList;

readonly class Blossom implements \IteratorAggregate {
    
    private \Closure $upload_authorized;
    
    public function __construct(private \nostriphant\NIP01\Key $server_key, private Blob\Factory $factory, callable $upload_authorized) {
        $this->upload_authorized = \Closure::fromCallable($upload_authorized);
    }
    
    static function fromPath(\nostriphant\NIP01\Key $server_key, string $path) : self {
        return new self($server_key, new Blob\Factory($path, null, []), fn(string $pubkey_hex) => true);
    }
    
    public function __invoke(UploadConstraints $constraints): self {
        
        $factory = $this->factory;
        if (isset($constraints->max_upload_size)) {
            $factory = Blob\Factory::recreate($factory, max_file_size: $constraints->max_upload_size);
        }
        if (isset($constraints->unsupported_content_types)) {
            $factory = Blob\Factory::recreate($factory, unsupported_media_types: $constraints->unsupported_content_types);
        }
        
        return new self($this->server_key, $factory, function(string $pubkey_hex, int $content_length, ?string $content_type, callable $unauthorized) use ($constraints) : bool|array {
            if (isset($constraints->allowed_pubkeys)) {
                if (in_array($pubkey_hex, $constraints->allowed_pubkeys) === false) {
                    return $unauthorized(401, '');
                }
            }
        
            if (isset($constraints->max_upload_size)) {
                if ($content_length > $constraints->max_upload_size) {
                    return $unauthorized(413, 'File too large. Max allowed size is '.$constraints->max_upload_size.' bytes.');
                }
            }
            
            if (isset($constraints->unsupported_content_types) && isset($content_type)) {
                if (in_array($content_type, $constraints->unsupported_content_types)) {
                    return $unauthorized(415, 'Unsupported file type.');
                }
                
                foreach (array_filter($constraints->unsupported_content_types, fn(string $unsupported_content_type) => str_ends_with($unsupported_content_type, '/*')) as $unsupported_content_type) {
                    list($category, $type) = explode('/', $unsupported_content_type, 2);
                    if (str_starts_with($content_type, $category . '/')) {
                        return $unauthorized(415, 'Unsupported file type.');
                    }
                }
            }
            return true;
        });
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
