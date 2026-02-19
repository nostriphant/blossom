<?php

namespace nostriphant\BlossomTests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class FeatureCase extends BaseTestCase
{
    const SOCKET = '127.0.0.1:8087';
    const RELAY_URL = 'http://' . self::SOCKET;
    const LOG_DIRECTORY = ROOT_DIR . "/logs";
    
    static mixed $blossom;
    
    static function request(string $method, string $path, $upload_resource = null, ?array $authorization = null, ?array $headers = []) : array {
        return \nostriphant\Blossom\request($method, str_starts_with($path, 'http') ? $path : self::RELAY_URL . $path, $upload_resource, $authorization, $headers);
    }
    
    static function writeFile(string $content) : string {
        $directory = files_directory();
        $hash = \nostriphant\Blossom\writeFile($directory, $content);
        expect($directory . DIRECTORY_SEPARATOR . $hash)->toBeFile();
        expect(file_get_contents($directory . DIRECTORY_SEPARATOR . $hash))->toBe($content);
        return $hash;
    }
    static function deleteFile(string $hash) : bool {
        $directory = files_directory();
        $result = \nostriphant\Blossom\deleteFile($directory, $hash);
        expect($result)->toBeTrue();
        expect($directory . DIRECTORY_SEPARATOR . $hash)->not()->toBeFile();
        return $result;
    }
    
    static function start_blossom(string $socket, string $output = self::LOG_DIRECTORY . "/blossom.log", string $errors = self::LOG_DIRECTORY . "/blossom-errors.log") {
        $descriptorspec = [
            0 => ["pipe", "r"],  
            1 => ["file", $output, "w"], 
            2 => ["file", $errors, "w"]
        ];
        
        list($host, $port) = explode(':', $socket, 2);
        $files_directory = 'files-' . $port;
        $files_path = \nostriphant\Blossom\data_directory() . DIRECTORY_SEPARATOR . $files_directory;
        is_dir($files_path) || mkdir($files_path);
    
        $process = proc_open([PHP_BINARY, '-S', $socket, './tests/blossom.php'], $descriptorspec, $pipes, ROOT_DIR, [
            'BLOSSOM_ALLOWED_PUBKEYS' => '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f',
            'FILES_DIRECTORY' => $files_directory,
            'MAX_CONTENT_LENGTH' => 100
        ]);

        fclose($pipes[0]);
        
        return new class($files_path, $process) {
            
            public function __construct(public string $files_directory, private $process) {
            
            }
            
            public function __invoke($remove_files = true) {
                proc_terminate($this->process);
                sleep(1);
                proc_close($this->process);
                
                if ($remove_files) {
                    destroy_directories($this->files_directory);
                    return is_dir($this->files_directory) && rmdir($this->files_directory);
                }
            }
        };
    }
    
}
