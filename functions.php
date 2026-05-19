<?php

namespace nostriphant\Blossom;

function writeFile(string $directory, string $content, ?string $owning_pubkey = null) : string {
    $hash = hash('sha256', $content);
    is_dir($directory) || mkdir($directory, recursive:true);
    file_put_contents($directory . DIRECTORY_SEPARATOR . $hash, $content);
    if (isset($owning_pubkey)) {
        $owners_directory = $directory . '/' . $hash . '.owners';
        is_dir($owners_directory) || mkdir($owners_directory);
        touch($owners_directory . '/' . $owning_pubkey);
    }
    return $hash;
}
function deleteFile(string $directory, string $hash) : bool {
    $hash_file = $directory . DIRECTORY_SEPARATOR . $hash;
    
    $owners_directory = $hash_file. '.owners';
    foreach (glob($owners_directory . '/*') as $owner) {
        unlink($owner);
    }
    is_dir($owners_directory) && rmdir($owners_directory);
    
    return is_file($hash_file) && unlink($hash_file);
}