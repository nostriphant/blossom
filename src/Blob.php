<?php


namespace nostriphant\Blossom;

final readonly class Blob {
    
    public function __construct(public string $path) {
        
    }

    public function __get(string $name): mixed {
        return match($name) {
            'type' => 'text/plain',
            'size' => filesize($this->path),
            'contents' => file_get_contents($this->path)
        };
    }
    
    public function __invoke(callable $exists, callable $missing): mixed {
        return match (file_exists($this->path)) {
            true => $exists($this),
            false => $missing($this)
        };
    }
    
}
