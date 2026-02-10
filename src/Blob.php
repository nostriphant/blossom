<?php


namespace nostriphant\Blossom;

final readonly class Blob {
    
    private \Closure $missing;
    private \Closure $exists;
    
    public function __construct(public string $path, callable $exists, callable $missing) {
        $this->missing = \Closure::fromCallable($missing);
        $this->exists = \Closure::fromCallable($exists);
    }

    public function __get(string $name): mixed {
        return match($name) {
            'type' => 'text/plain',
            'size' => filesize($this->path),
            'contents' => file_get_contents($this->path),
            default => null
        };
    }
    
    public function __invoke(): mixed {
        return match (file_exists($this->path)) {
            true => ($this->exists)($this),
            false => ($this->missing)($this)
        };
    }
    
}
