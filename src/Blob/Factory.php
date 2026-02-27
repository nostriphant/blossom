<?php

namespace nostriphant\Blossom\Blob;

class Factory {
    
    private \Closure $url_register;
    
    public function __construct(private string $path, callable $url_register, private ?int $max_file_size) {
        $this->url_register = \Closure::fromCallable($url_register);
    }
    
    static function recreate(self $factory, mixed ...$new_args) : self {
        return new self(...array_merge(get_object_vars($factory), $new_args));
    }
    
    public function __invoke(?string $hash): mixed {
        if (isset($hash) === false) {
            return new Uncreated(new \nostriphant\Blossom\VFS\Directory($this->path, $this->max_file_size), $this->url_register);
        }
        $file = new \nostriphant\Blossom\VFS\File($this->path . DIRECTORY_SEPARATOR . $hash);
        $uri = ($this->url_register)($hash);
        if ($file->exists === false) {
            return new \nostriphant\Blossom\Blob\Missing($file, $uri);
        }
        return new \nostriphant\Blossom\Blob($file, $uri);
    }
}
