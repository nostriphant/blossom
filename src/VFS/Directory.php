<?php

namespace nostriphant\Blossom\VFS;

readonly class Directory extends Node {
    
    public function __construct(string $path) {
        parent::__construct($path);
    }
    
    static function fromFile(File $file) : self {
        return new self(dirname($file->path));
    }
    
    public function __invoke(string $pubkey_owner, callable $stream, string $hash) : File {
        $temp = tempnam($this->path, "buffer.");
        
        try {
            $written = 0;
            $handle = fopen($temp, 'wb');
            while (($buffer = $stream($written)) !== false) {
                $written += fwrite($handle, $buffer);
            }
        } catch (\Exception $e) {
            unlink($temp);
            throw $e;
        } finally {
            fclose($handle);
        }
        
        $actual_hash = hash_file('sha256', $temp);
        if ($actual_hash !== $hash) {
            throw new \nostriphant\Blossom\Exception(403, 'Authorized hash ('.$hash.')  does not match hash of contents ('.$actual_hash.').');
        }
        
        $target_location = $this->path . DIRECTORY_SEPARATOR . $hash;
        
        if (file_exists($target_location) === false) {
            rename($temp, $target_location);
            mkdir($target_location . '.owners');
        }
        
        is_file($temp) === false || unlink($temp);
        touch($target_location . '.owners' . DIRECTORY_SEPARATOR . $pubkey_owner);
        return new File($target_location);
    }
}
