<?php

namespace nostriphant\Blossom\Blob;


class Uncreated {
    
    private \Closure $unsupported_media_types;
    
    public function __construct(private string $path, private ?int $max_file_size, callable $unsupported_media_types) {
        $this->unsupported_media_types = \Closure::fromCallable($unsupported_media_types);
    }
    
    public function __invoke(string $pubkey_hex, callable $stream): \nostriphant\Blossom\Blob {
        $temp = tempnam($this->path, "buffer.");
        
        $written = 0;
        $handle = fopen($temp, 'wb');
        foreach ($stream() as $buffer) {
            $written += fwrite($handle, $buffer);
            if ($written > $this->max_file_size) {
                fclose($handle);
                unlink($temp);
                return new \nostriphant\Blossom\Blob\Failed(413, 'Filesize larger than max allowed file size.');
            }
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
