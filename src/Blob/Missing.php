<?php

namespace nostriphant\Blossom\Blob;


class Missing implements Creatable {
    public function __construct(private string $path) {
        
    }
    
    #[\Override]
    public function __invoke(callable $stream): \nostriphant\Blossom\Blob {
        
        $handle = fopen($this->path, 'wb');
        foreach ($stream() as $buffer) {
            fwrite($handle, $buffer);
        }
        fclose($handle);
    
        return new \nostriphant\Blossom\Blob($this->path);
    }
    
}
