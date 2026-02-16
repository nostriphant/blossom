<?php

namespace nostriphant\Blossom\Blob;


class Uncreated {
    public function __construct(private string $path) {
        
    }
    
    public function __invoke(string $pubkey_hex, callable $stream): \nostriphant\Blossom\Blob {
        $temp = tempnam($this->path, "buffer.");
        
        $handle = fopen($temp, 'wb');
        foreach ($stream() as $buffer) {
            fwrite($handle, $buffer);
        }
        fclose($handle);
        
        $target_location = $this->path . DIRECTORY_SEPARATOR . hash_file('sha256', $temp);
        if (file_exists($target_location) === false) {
            rename($temp, $target_location);
            mkdir($target_location . '.owners');
        }
        
        touch($target_location . '.owners' . DIRECTORY_SEPARATOR . $pubkey_hex);
        
        return new \nostriphant\Blossom\Blob($target_location);
    }
    
}
