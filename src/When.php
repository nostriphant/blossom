<?php


namespace nostriphant\Blossom;

final readonly class When {
    
    private \Closure $test;
    private \Closure $missing;
    private \Closure $exists;
    
    public function __construct(callable $test, public string $path, callable $true, callable $false) {
        $this->test = \Closure::fromCallable($test);
        $this->missing = \Closure::fromCallable($false);
        $this->exists = \Closure::fromCallable($true);
    }
    
    public function __invoke(): mixed {
        return match (($this->test)($this->path)) {
            true => ($this->exists)(new Blob($this->path)),
            false => ($this->missing)()
        };
    }
    
}
