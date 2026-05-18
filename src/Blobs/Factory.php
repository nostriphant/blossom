<?php

namespace nostriphant\Blossom\Blobs;

class Factory {
    
    private \Closure $url_register;
    
    public function __construct(private string $files_directory, callable $url_register) {
        $this->url_register = \Closure::fromCallable($url_register);
    }
    
    static function recreate(self $factory, mixed ...$new_args) : self {
        return new self(...array_merge(get_object_vars($factory), $new_args));
    }
    
    public function __invoke(string $pubkey, ?int $limit, ?string $cursor): mixed {
        $blobs = [];
        $after_cursor = isset($cursor) === false;
        $matched_pubkeys = glob($this->files_directory . '/*.owners/' . $pubkey);
        sort($matched_pubkeys);
        foreach ($matched_pubkeys as $matched_pubkey) {
            $directory = dirname($matched_pubkey);
            $hash = basename($directory, '.owners');
            
            if ($after_cursor) {
                // include file
            } elseif ($hash === $cursor) {
                $after_cursor = true;
                continue;
            } else {
                continue;
            }
            
            $blobs[] = new \nostriphant\Blossom\Blob(new \nostriphant\Blossom\VFS\File(dirname($directory) . '/'. $hash), ($this->url_register)($hash));
            if (count($blobs) === $limit) {
                break;
            }
        }
        return $blobs;
    }
}
