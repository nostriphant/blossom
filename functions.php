<?php

namespace nostriphant\Blossom;

function request(string $method, string $uri, $upload_resource = null, ?array $authorization = null, ?array $headers = []) : array {
    if (isset($authorization)) {
        $sender_key = Key::fromHex($authorization['key'] ?? 'a71a415936f2dd70b777e5204c57e0df9a6dffef91b3c78c1aa24e54772e33c3');
        unset($authorization['key']);
        
        $tags = [];
        if (isset($authorization['expiration']) === false) {
            $tags[] = ["expiration", time() + 3600];
        }
        foreach ($authorization as $tag => $value) {
            $tags[] = [$tag, $value];
        }

    }
    $response = \nostriphant\HTTP\request($method, $uri, $upload_resource, new \nostriphant\HTTP\Authorization\Nostr($sender_key), $headers);
    return [$response->protocol, $response->status, $response->headers, $response->body];
}

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