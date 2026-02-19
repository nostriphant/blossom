<?php


namespace nostriphant\Blossom\VFS;

readonly class Node {
    public bool $exists;
    public ?int $created;
    
    
    public function __construct(public string $path) {
        $this->exists = file_exists($path);
        if ($this->exists) {
            $this->created = filectime($path);
        } else {
            $this->created = null;
        }
    }
}
