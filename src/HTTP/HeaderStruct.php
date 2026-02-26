<?php

namespace nostriphant\Blossom\HTTP;

readonly class HeaderStruct implements \IteratorAggregate, \ArrayAccess {
    
    private array $headers;
    
    /**
     * Raw is how headers are received/sent from a party, eg: Content-Type: text/plain
     * @param array $headers
     * @return array
     */
    public function __construct(array $response_headers) {
        $headers = [];
        foreach(array_filter($response_headers) as $response_header) {
            if (str_starts_with($response_header, 'HTTP/')) {
                list($protocol, $status) = explode(' ', $response_header, 2);
                if (str_contains($status, ' ')) {
                    list($status,) = explode(' ', $status, 2);
                } elseif ($status === '100') {
                    continue;
                }
                
                $headers['status'] = [$status];
                $headers['protocol'] = [$protocol];
                continue;
            }
            
            list($header, $value) = explode(':', $response_header, 2);
            $headers[strtolower($header)] = array_map('trim', explode(';', $value ?? ''));
        };
        $this->headers = $headers;
    }
    
    #[\Override]
    public function getIterator(): \Traversable {
        yield from $this->headers;
    }
    
    #[\Override]
    public function offsetExists(mixed $offset): bool {
        return array_key_exists($offset, $this->headers);
    }
    
    #[\Override]
    public function offsetGet(mixed $offset): mixed {
        return count($this->headers[$offset]) === 1 ? $this->headers[$offset][0] : $this->headers[$offset];
    }
    
    #[\Override]
    public function offsetSet(mixed $offset, mixed $value): void {
        
    }
    
    #[\Override]
    public function offsetUnset(mixed $offset): void {
        
    }
    
    public function __get(string $name): array {
        return $this->offsetGet(str_replace('_', '-', $name));
    }
}
