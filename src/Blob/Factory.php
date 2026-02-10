<?php
namespace nostriphant\Blossom\Blob;

readonly class Factory {
    
    private \Closure $when_factory;
    
    public function __construct(string $path, callable $missing) {
        $this->when_factory = fn(callable $exists) => fn(string $hash) => new \nostriphant\Functional\When('file_exists', fn(string $blob_path) => $exists(new \nostriphant\Blossom\Blob($blob_path)), $missing)($path . DIRECTORY_SEPARATOR . $hash);
    }
    
    public function __invoke(callable $exists) : callable {
        return ($this->when_factory)($exists);
    }
    
}
