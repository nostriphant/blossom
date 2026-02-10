<?php
namespace nostriphant\Blossom\Blob;

readonly class Factory {
    
    public function __construct(private string $path, private \Closure $missing) {
    }
    
    public function __invoke(callable $exists) : callable {
        $when = new \nostriphant\Blossom\When('file_exists', fn(string $path) => $exists(new \nostriphant\Blossom\Blob($path)), $this->missing);
        return fn(string $hash) => $when($this->path . DIRECTORY_SEPARATOR . $hash);
    }
    
}
