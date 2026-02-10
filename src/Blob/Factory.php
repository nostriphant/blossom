<?php
namespace nostriphant\Blossom\Blob;

readonly class Factory {
    
    public function __construct(private string $path, private \Closure $missing) {
        
    }
    
    public function __invoke(string $hash, callable $exists) : \nostriphant\Blossom\When {
        $path = $this->path . DIRECTORY_SEPARATOR . $hash;
        return new \nostriphant\Blossom\When(fn() => file_exists($path), fn() => $exists(new \nostriphant\Blossom\Blob($path)), fn() => ($this->missing)($path));
    }
    
}
