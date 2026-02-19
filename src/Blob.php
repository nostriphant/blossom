<?php


namespace nostriphant\Blossom;

readonly class Blob {
    
    public bool $exists;
    public string $url;
    public int $uploaded;
    public array $owners;
    
    public function __construct(private VFS\File $file) {
        $this->exists = $file->exists;
        if ($file->exists) {
            $this->url = "http://127.0.0.1:8087/" . $this->file->sha256;
            $this->uploaded = $file->created;
            $this->owners = array_map('basename', glob($file->path . '.owners/*'));
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
        return $this->file->$name;
    }
    
    public function __invoke(): array {
        return [
            'headers' => [ 
                'Content-Type' => $this->file->type,
                'Content-Length' => $this->file->size
            ],
            'body' => VFS\File::read($this->file)
        ];
    }
}
