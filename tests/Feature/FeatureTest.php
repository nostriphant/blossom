<?php

use \nostriphant\BlossomTests\FeatureCase;

beforeAll(function() use (&$blossom) : void {
    FeatureCase::$blossom = FeatureCase::start_blossom(FeatureCase::SOCKET);
    define('FILES_DIRECTORY', FeatureCase::$blossom->files_directory);
});

    
foreach (glob(__DIR__ . '/BUD*.php') as $test_file) {
    describe(basename($test_file, '.php'), function() use ($test_file) {
        require $test_file;
    });
}    


afterAll(function() use (&$blossom): void {
    FeatureCase::$blossom();
});