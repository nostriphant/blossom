<?php

namespace nostriphant\BlossomTests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class FeatureCase extends BaseTestCase
{   
    static mixed $blossom;
    
    static function request(string $method, string $path, $upload_resource = null, ?array $authorization = null, ?array $headers = []) : \nostriphant\HTTP\ServerResponse {
        $authorization['key'] ??= 'a71a415936f2dd70b777e5204c57e0df9a6dffef91b3c78c1aa24e54772e33c3';
        $authorization['pubkey'] ?? \nostriphant\NIP01\Key::derivePublicKey(\nostriphant\NIP01\Key::fromHex($authorization['key']));
        
        return \nostriphant\HTTP\request($method, str_starts_with($path, 'http') ? $path : self::$blossom->url . $path, $upload_resource, ['nostr', $authorization], $headers);
    }
    
    static function start_blossom(string $socket, string $output, string $errors) {
        $logs_directory = ROOT_DIR . "/logs";
        is_dir($logs_directory) || mkdir($logs_directory);
        
        $descriptorspec = [
            0 => ["pipe", "r"],  
            1 => ["file", $logs_directory . DIRECTORY_SEPARATOR . $output, "w"], 
            2 => ["file", $logs_directory . DIRECTORY_SEPARATOR . $errors, "w"]
        ];
        
        list($host, $port) = explode(':', $socket, 2);
        $data_directory = \nostriphant\Blossom\data_directory() . '-' . $port;
        is_dir($data_directory) || mkdir($data_directory);
    
        $url = 'http://' . $socket;
        $process = proc_open([PHP_BINARY, '-S', $socket, '-d', 'variables_order=EGPCS', './tests/blossom.php'], $descriptorspec, $pipes, ROOT_DIR, [
            'BLOSSOM_SERVER_URL' => $url,
            'BLOSSOM_DATA_DIRECTORY' => $data_directory,
            'BLOSSOM_ALLOWED_PUBKEYS' => '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f',
            'MAX_CONTENT_LENGTH' => 100
        ]);

        fclose($pipes[0]);
        
        return new class($data_directory . DIRECTORY_SEPARATOR . 'files', $url, $process) {
            
            public function __construct(public string $files_directory, public string $url, private $process) {
            
            }
            
            public function __invoke() {
                proc_terminate($this->process);
                proc_close($this->process);
                
                \nostriphant\Blossom\destroy_directories($this->files_directory);
                is_dir($this->files_directory) && rmdir($this->files_directory);
                $data_directory = dirname($this->files_directory);
                return is_dir($data_directory) && rmdir($data_directory);
            }
        };
    }
    
}
