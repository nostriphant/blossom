<?php

namespace nostriphant\Blossom\HTTP;

readonly class ServerRequest implements \IteratorAggregate {
    
    public mixed $body;
    public ?\nostriphant\NIP01\Event $authorization;
    
    public function __construct(
            public array $headers,
            public array $attributes,
            mixed $body
    ) {
        
        if (isset($headers['HTTP_AUTHORIZATION'])) {
            list($type, $base64) = explode(' ', trim($headers['HTTP_AUTHORIZATION']));
            if (strcasecmp($type, 'nostr') === 0) {
                $this->authorization = new \nostriphant\NIP01\Event(...\nostriphant\NIP01\Nostr::decode(base64_decode($base64)));
            }
        }
        
        
        if (is_string($body)) {
            $resource = fopen("php://memory", "r+");
            fwrite($resource, $this->body);
            fseek($resource, 0);
            $this->body = $resource;
        } elseif (is_resource($body)) {
            $this->body = $body;
        }
    }
    
    public function __invoke(): \Generator {
        while (feof($this->body) === false) {
            yield fread($this->body, 1024);
        }
    }
    
    #[\Override]
    public function getIterator(): \Traversable {
        return $this();
    }
}
