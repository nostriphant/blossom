<?php

use \nostriphant\BlossomTests\FeatureCase;

$blossom;
beforeAll(function() use (&$blossom) : void {
    $blossom = FeatureCase::start_blossom(FeatureCase::SOCKET);
    define('FILES_DIRECTORY', $blossom->files_directory);
});

    
foreach (glob(__DIR__ . '/BUD*.php') as $test_file) {
    //describe(basename($test_file, '.php'), function() use ($test_file) {
        require $test_file;
    //});
}    


afterAll(function() use (&$blossom): void {
    $blossom();
});