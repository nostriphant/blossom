<?php

namespace nostriphant\Blossom\VFS;

readonly class Directory extends Node {
    
    public function __construct(string $path, public int $max_file_size) {
        parent::__construct($path);
    }
    
    static function fromFile(File $file) : self {
        return new self(dirname($file->path));
    }
    
    public function __invoke(string $pubkey_owner, $stream, string $hash) : File {
        $temp = tempnam($this->path, "buffer.");
        
        $written = 0;
        $handle = fopen($temp, 'wb');
        while (feof($stream) === false) {
            $buffer = fread($stream, 1024);
            $written += fwrite($handle, $buffer);
            if ($written > $this->max_file_size) {
                fclose($handle);
                unlink($temp);
                throw new \nostriphant\Blossom\Exception(413, 'Filesize larger than max allowed file size.');
            }
        }
        fclose($handle);
        
        if (hash_file('sha256', $temp) !== $hash) {
            throw new \nostriphant\Blossom\Exception(403, 'Authorized hash does not much mirrored file.');
        }
        
        $target_location = $this->path . DIRECTORY_SEPARATOR . $hash;
        
        if (file_exists($target_location) === false) {
            rename($temp, $target_location);
            mkdir($target_location . '.owners');
        }
        
        touch($target_location . '.owners' . DIRECTORY_SEPARATOR . $pubkey_owner);
        return new File($target_location);
    }
}
