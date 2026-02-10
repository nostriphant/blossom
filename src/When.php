<?php


namespace nostriphant\Blossom;

final readonly class When {
    
    private \Closure $test;
    private \Closure $false;
    private \Closure $true;
    
    public function __construct(callable $test, callable $true, callable $false) {
        $this->test = \Closure::fromCallable($test);
        $this->false = \Closure::fromCallable($false);
        $this->true = \Closure::fromCallable($true);
    }
    
    public function __invoke(): mixed {
        return (match (($this->test)()) {
            true => ($this->true),
            false => ($this->false)
        })();
    }
    
}
