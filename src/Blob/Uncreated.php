<?php

namespace nostriphant\Blossom\Blob;


class Uncreated implements Creatable {
    public function __construct(private string $path) {
        
    }
    
    #[\Override]
    public function __invoke(callable $stream): \nostriphant\Blossom\Blob {
        $temp = tempnam($this->path, "buffer.");
        
        $handle = fopen($temp, 'wb');
        foreach ($stream() as $buffer) {
            error_log('DATA: ' . $buffer);
            fwrite($handle, $buffer);
        }
        fclose($handle);
        
        $target_location = $this->path . DIRECTORY_SEPARATOR . hash_file('sha256', $temp);
        rename($temp, $target_location);
        return new \nostriphant\Blossom\Blob($target_location);
    }
    
}
