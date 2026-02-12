<?php
namespace nostriphant\Blossom\Blob;

readonly class Factory {
    
    private \Closure $when_factory;
    
    public function __construct(string $path) {
        
        $blob_existing = fn(callable $exists) => fn(string $blob_path) => $exists(new \nostriphant\Blossom\Blob($blob_path));
        $blob_missing = fn(callable $missing) => fn(string $blob_path) => $missing(new \nostriphant\Blossom\Blob\Missing($blob_path));
        
        $this->when_factory = fn(callable $exists, callable $missing) => new \nostriphant\Functional\When(
                        fn() => func_num_args() === 1 && is_String(func_get_arg(0)),
                        fn(string $hash) => new \nostriphant\Functional\When(
                                'file_exists', 
                                $blob_existing($exists), 
                                $blob_missing($missing)
                        )($path . DIRECTORY_SEPARATOR . $hash), 
                        fn() =>  $missing(new \nostriphant\Blossom\Blob\Uncreated($path))
                    );
                    
    }
    
    public function __invoke(callable $exists, callable $missing) : callable {
        return ($this->when_factory)($exists, $missing);
    }
    
}
