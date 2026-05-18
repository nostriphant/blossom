<?php

namespace nostriphant\Blossom\VFS;

readonly class File extends Node {
    
    public ?string $sha256;
    public ?string $type;
    public ?int $size;
    
    
    public function __construct(string $path) {
        parent::__construct($path);
        $this->sha256 = basename($path);
        if ($this->exists) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $this->type = finfo_file($finfo, $path);
            finfo_close($finfo);
            $this->size = filesize($this->path);
        } else {
            $this->type = null;
            $this->size = null;
        }
    }
    
    static function read(self $file): string {
        return file_get_contents($file->path);
    }
}
