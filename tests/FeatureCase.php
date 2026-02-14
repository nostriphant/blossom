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
    
    static public $process;
    static public $runners = 0;
        
    static function request(string $method, string $path, $upload_resource = null, ?array $authorization = null) : array {
        return \nostriphant\Blossom\request($method, self::RELAY_URL . $path, $upload_resource, $authorization);
    }
    
    static function writeFile(string $content) {
        $directory = files_directory();
        $hash = \nostriphant\Blossom\writeFile($directory, $content);
        expect($directory . DIRECTORY_SEPARATOR . $hash)->toBeFile();
        expect(file_get_contents($directory . DIRECTORY_SEPARATOR . $hash))->toBe($content);
        return $hash;
    }
}
