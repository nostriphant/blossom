<?php

namespace nostriphant\BlossomTests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class FeatureCase extends BaseTestCase
{
    const SOCKET = '127.0.0.1:8087';
    const RELAY_URL = 'http://' . self::SOCKET;
    const LOG_DIRECTORY = ROOT_DIR . "/logs";
    const LOG_OUTPUT = self::LOG_DIRECTORY . "/blossom.log";
    const LOG_ERRORS = self::LOG_DIRECTORY . "/blossom-errors.log";
    
    static private $process;
    
    static function relay_output() {
        return file_get_contents(self::LOG_OUTPUT);
    }
    static function relay_errors() {
        return file_get_contents(self::LOG_ERRORS);
    }
    static function relay_process() {
        if (isset(self::$process) === false) {
            is_dir(self::LOG_DIRECTORY) || mkdir(self::LOG_DIRECTORY);
            
            $descriptorspec = [
                0 => ["pipe", "r"],  
                1 => ["file", self::LOG_OUTPUT, "w"], 
                2 => ["file", self::LOG_ERRORS, "w"]
            ];
            self::$process = proc_open([PHP_BINARY, '-S', self::SOCKET, './tests/blossom.php'], $descriptorspec, $pipes, ROOT_DIR, []);

            expect(self::$process)->toBeResource(self::relay_errors());
            fclose($pipes[0]);

            while (str_contains(self::relay_errors(), 'Development Server (' . self::RELAY_URL . ') started') === false){ }
        }

        return self::$process;
    }
    
    static function end_relay_process() {
        proc_terminate(self::$process);
        sleep(1);

        proc_close(self::$process);

        expect(self::relay_errors())->toBeEmpty();
        unlink(self::LOG_ERRORS);
        unlink(self::LOG_OUTPUT);
    }
    
    static function request(string $method, string $path, $upload_resource = null, array $headers = []) : array {
        return \nostriphant\Blossom\request($method, self::RELAY_URL . $path, $upload_resource, $headers);
    }
    
    static function writeFile(string $content) {
        $directory = files_directory();
        $hash = \nostriphant\Blossom\writeFile($directory, $content);
        expect($directory . DIRECTORY_SEPARATOR . $hash)->toBeFile();
        expect(file_get_contents($directory . DIRECTORY_SEPARATOR . $hash))->toBe($content);
        return $hash;
    }
}
