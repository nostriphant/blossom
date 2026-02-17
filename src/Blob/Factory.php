<?php

namespace nostriphant\Blossom\Blob;

class Factory {
    public function __construct(private string $path, private ?int $max_file_size) {
        
    }
    
    static function recreate(self $factory, mixed ...$new_args) : self {
        return new self(...array_merge(get_object_vars($factory), $new_args));
    }
    
    public function __invoke(?string $hash = null): mixed {
        if (isset($hash) === false) {
            return new Uncreated($this->path, $this->max_file_size);
        } elseif ($hash === "remote") {
            return new Remote($this->path, $this->max_file_size);
        }
        return new \nostriphant\Blossom\Blob($this->path . DIRECTORY_SEPARATOR . $hash);
    }
}
