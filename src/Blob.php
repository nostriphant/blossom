<?php


namespace nostriphant\Blossom;

readonly class Blob {
    
    public bool $exists;
    public string $sha256;
    public string $type;
    public int $size;
    public string $url;
    public int $uploaded;
    public array $owners;
    
    public function __construct(private string $path) {
        $this->exists = file_exists($path);
        $this->sha256 = basename($path);
        if ($this->exists) {
            $this->type = 'text/plain';
            $this->size = filesize($this->path);
            $this->url = "http://127.0.0.1:8087/" . $this->sha256;
            $this->uploaded = filectime($path);
            
            $this->owners = array_map('basename', glob($this->path . '.owners/*'));
        }
    }
    
    static function delete(self $blob, string $pubkey_hex) : bool {
        $owners_directory = $blob->path . '.owners';
        $owner_file = $owners_directory . DIRECTORY_SEPARATOR . $pubkey_hex;
        is_file($owner_file) && unlink($owner_file);
        
        if (count(glob($owners_directory . '/*')) > 0) {
            return true;
        }
        
        is_dir($owners_directory) && rmdir($owners_directory);
        return unlink($blob->path);
    }
    
    public function __get(string $name): mixed {
        return match($name) {
            'contents' => file_get_contents($this->path),
            default => null
        };
    }
}
