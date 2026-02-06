<?php


namespace nostriphant\Blossom;

final readonly class Blob {
    
    private \Closure $missing;
    
    public function __construct(public string $path, callable $missing) {
        $this->missing = \Closure::fromCallable($missing);
    }

    public function __get(string $name): mixed {
        return match($name) {
            'type' => 'text/plain',
            'size' => filesize($this->path),
            'contents' => file_get_contents($this->path)
        };
    }
    
    public function __invoke(callable $exists): mixed {
        return match (file_exists($this->path)) {
            true => $exists($this),
            false => ($this->missing)($this)
        };
    }
    
}
