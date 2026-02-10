<?php
namespace nostriphant\Blossom\Blob;

readonly class Factory {
    
    public function __construct(private string $path, private \Closure $missing) {
        
    }
    
    public function __invoke(string $hash, callable $exists) : \nostriphant\Blossom\When {
        return new \nostriphant\Blossom\When('file_exists' , $this->path . DIRECTORY_SEPARATOR . $hash, $exists, $this->missing);
    }
    
}
