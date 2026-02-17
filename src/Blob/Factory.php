<?php

namespace nostriphant\Blossom\Blob;

class Factory {
    
    private \Closure $unsupported_media_types;
    
    public function __construct(private string $path, private ?int $max_file_size, callable $unsupported_media_types) {
        $this->unsupported_media_types = \Closure::fromCallable($unsupported_media_types);
        
    }
    
    static function recreate(self $factory, mixed ...$new_args) : self {
        return new self(...array_merge(get_object_vars($factory), $new_args));
    }
    
    public function __invoke(?string $hash = null): mixed {
        if (isset($hash) === false) {
            return new Uncreated($this->path, $this->max_file_size, $this->unsupported_media_types);
        } elseif ($hash === "remote") {
            return new Remote($this->path, $this->max_file_size, $this->unsupported_media_types);
        }
        return new \nostriphant\Blossom\Blob($this->path . DIRECTORY_SEPARATOR . $hash);
    }
}
