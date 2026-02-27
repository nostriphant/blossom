<?php

use \nostriphant\BlossomTests\FeatureCase;

beforeAll(function() : void {
    FeatureCase::$blossom = FeatureCase::start_blossom('127.0.0.1:8087', ROOT_DIR . "/logs/blossom-errors-8087.log", ROOT_DIR . "/logs/blossom-errors-8087.log");
    define('FILES_DIRECTORY', FeatureCase::$blossom->files_directory);
});

    
foreach (glob(__DIR__ . '/BUD*.php') as $test_file) {
    describe(basename($test_file, '.php'), function() use ($test_file) {
        require $test_file;
    });
}    


afterAll(function() : void {
    (FeatureCase::$blossom)();
});