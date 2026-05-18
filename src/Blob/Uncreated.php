<?php

namespace nostriphant\Blossom\Blob;


class Uncreated {
    
    private \Closure $url_register;
    
    public function __construct(private \nostriphant\Blossom\VFS\Directory $directory, callable $url_register, private int $max_file_size) {
        $this->url_register = \Closure::fromCallable($url_register);
    }
    
    public function __invoke(string $pubkey_hex, mixed $stream, string $hash, ?string $uri) : array {
        try {
            $file = ($this->directory)($pubkey_hex, function(int $written) use ($stream) {
                if ($written > $this->max_file_size) {
                    throw new \nostriphant\Blossom\Exception(413, 'Filesize larger than max allowed file size.');
                }
                return feof($stream) ? false : fread($stream, 1024);
            }, $hash);
            
            $blob = new \nostriphant\Blossom\Blob($file, ($this->url_register)($hash, $uri));
            return [
                'status' => 201,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Content-Location' => '/' . $blob->sha256
                ],
                'body' => $blob
            ];
        } catch (\nostriphant\Blossom\Exception $e) {
            return ['status' => $e->getCode(), 'headers' => ['x-reason' => $e->getMessage()]];
        }
    }
    
}
