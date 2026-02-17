<?php

namespace nostriphant\Blossom\Blob;


class Uncreated {
    public function __construct(private string $path, private ?int $max_file_size) {
        
    }
    
    public function __invoke(string $pubkey_hex, callable $stream): \nostriphant\Blossom\Blob {
        $temp = tempnam($this->path, "buffer.");
        
        $written = 0;
        $handle = fopen($temp, 'wb');
        $failed = false;
        foreach ($stream() as $buffer) {
            $written += fwrite($handle, $buffer);
            if ($written > $this->max_file_size) {
                $failed = [413, 'Filesize larger than max file size.'];
                break;
            }
        }
        fclose($handle);
        
        if ($failed !== false) {
            unlink($temp);
            return new \nostriphant\Blossom\Blob\Failed(...$failed);
        }
        
        $target_location = $this->path . DIRECTORY_SEPARATOR . hash_file('sha256', $temp);
        if (file_exists($target_location) === false) {
            rename($temp, $target_location);
            mkdir($target_location . '.owners');
        }
        
        touch($target_location . '.owners' . DIRECTORY_SEPARATOR . $pubkey_hex);
        
        return new \nostriphant\Blossom\Blob($target_location);
    }
    
}
