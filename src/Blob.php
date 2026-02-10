<?php


namespace nostriphant\Blossom;

final readonly class Blob {
    
    private \Closure $test;
    private \Closure $missing;
    private \Closure $exists;
    
    public function __construct(callable $test, public string $path, callable $exists, callable $missing) {
        $this->test = \Closure::fromCallable($test);
        $this->missing = \Closure::fromCallable($missing);
        $this->exists = \Closure::fromCallable($exists);
    }
    
    public function __invoke(): mixed {
        return match (($this->test)($this->path)) {
            true => ($this->exists)(fn(string $name) => match($name) {
                'type' => 'text/plain',
                'size' => filesize($this->path),
                'contents' => file_get_contents($this->path),
                default => null
            }),
            false => ($this->missing)()
        };
    }
    
}
