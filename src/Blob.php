<?php


namespace nostriphant\Blossom;

readonly class Blob {
    
    public bool $exists;
    public string $sha256;
    public string $type;
    public int $size;
    public string $url;
    public int $uploaded;
    
    public function __construct(private string $path) {
        $this->exists = file_exists($path);
        $this->sha256 = basename($path);
        $this->type = 'text/plain';
        $this->size = filesize($this->path);
        $this->url = "http://127.0.0.1:8087/" . $this->sha256;
        $this->uploaded = filectime($path);
    }
    
    static function delete(self $blob) : bool {
        return unlink($blob->path);
    }
    
    public function __get(string $name): mixed {
        return match($name) {
            'contents' => file_get_contents($this->path),
            default => null
        };
    }
}
