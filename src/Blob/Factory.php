<?php

namespace nostriphant\Blossom\Blob;

class Factory {
    
    public function __construct(private string $path, private ?int $max_file_size) {
    }
    
    static function recreate(self $factory, mixed ...$new_args) : self {
        return new self(...array_merge(get_object_vars($factory), $new_args));
    }
    
    public function __invoke(string $hash): mixed {
        if ($hash === 'upload') {
            return new Uncreated(new \nostriphant\Blossom\VFS\Directory($this->path, $this->max_file_size));
        } elseif ($hash === "remote") {
            return new Remote(new \nostriphant\Blossom\VFS\Directory($this->path, $this->max_file_size));
        }
        return new \nostriphant\Blossom\Blob(new \nostriphant\Blossom\VFS\File($this->path . DIRECTORY_SEPARATOR . $hash));
    }
}
