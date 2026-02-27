<?php

namespace nostriphant\BlossomTests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class FeatureCase extends BaseTestCase
{   
    static mixed $blossom;
    
    static function request(string $method, string $path, $upload_resource = null, ?array $authorization = null, ?array $headers = []) : array {
        return \nostriphant\Blossom\request($method, str_starts_with($path, 'http') ? $path : self::$blossom->url . $path, $upload_resource, $authorization, $headers);
    }
    
    static function start_blossom(string $socket, string $output, string $errors) {
        $descriptorspec = [
            0 => ["pipe", "r"],  
            1 => ["file", $output, "w"], 
            2 => ["file", $errors, "w"]
        ];
        
        list($host, $port) = explode(':', $socket, 2);
        $data_directory = \nostriphant\Blossom\data_directory() . '-' . $port;
        is_dir($data_directory) || mkdir($data_directory);
    
        $url = 'http://' . $socket;
        $process = proc_open([PHP_BINARY, '-S', $socket, './tests/blossom.php'], $descriptorspec, $pipes, ROOT_DIR, [
            'BLOSSOM_SERVER_URL' => $url,
            'BLOSSOM_DATA_DIRECTORY' => $data_directory,
            'BLOSSOM_ALLOWED_PUBKEYS' => '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f',
            'MAX_CONTENT_LENGTH' => 100
        ]);

        fclose($pipes[0]);
        
        return new class($data_directory . DIRECTORY_SEPARATOR . 'files', $url, $process) {
            
            public function __construct(public string $files_directory, public string $url, private $process) {
            
            }
            
            public function __invoke($remove_files = true) {
                proc_terminate($this->process);
                proc_close($this->process);
                
                if ($remove_files) {
                    destroy_directories($this->files_directory);
                    is_dir($this->files_directory) && rmdir($this->files_directory);
                    $data_directory = dirname($this->files_directory);
                    return is_dir($data_directory) && rmdir($data_directory);
                }
            }
        };
    }
    
}
