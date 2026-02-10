<?php


namespace nostriphant\Blossom;

readonly class Blob {
    public function __construct(private string $path) {
        
    }
    
    public function __get(string $name): mixed {
        return match($name) {
            'type' => 'text/plain',
            'size' => filesize($this->path),
            'contents' => file_get_contents($this->path),
            default => null
        };
    }
}
