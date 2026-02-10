<?php


namespace nostriphant\Blossom;

final readonly class Blob {
    
    private \Closure $missing;
    private \Closure $exists;
    
    public function __construct(public string $path, callable $exists, callable $missing) {
        $this->missing = \Closure::fromCallable($missing);
        $this->exists = \Closure::fromCallable($exists);
    }
    
    public function __invoke(string $hash): mixed {
        $path =  $this->path . DIRECTORY_SEPARATOR . $hash;
        return match (file_exists($path)) {
            true => ($this->exists)(fn(string $name) => match($name) {
                'type' => 'text/plain',
                'size' => filesize($path),
                'contents' => file_get_contents($path),
                default => null
            }),
            false => ($this->missing)()
        };
    }
    
}
