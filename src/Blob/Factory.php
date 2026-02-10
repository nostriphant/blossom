<?php
namespace nostriphant\Blossom\Blob;

readonly class Factory {
    
    public function __construct(private string $path, private \Closure $missing) {
        
    }
    
    public function __invoke(callable $exists) : \nostriphant\Blossom\Blob {
        return new \nostriphant\Blossom\Blob($this->path, $exists, $this->missing);
    }
    
}
