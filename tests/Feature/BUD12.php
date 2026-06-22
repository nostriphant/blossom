<?php

namespace nostriphant\BlossomTests\Feature;

use \nostriphant\BlossomTests\FeatureCase;

    
describe("OPTIONS /list/*", function() {
    it('OPTIONS on /list returns a 400 status code', function () {
        $hash = \nostriphant\Blossom\writeFile(FILES_DIRECTORY, 'Hello World!');
        $response = FeatureCase::request('OPTIONS', '/list');
        expect($response->status)->toBe('404');
        expect($response->body)->toBeEmpty();
    });
    
    it('For preflight (OPTIONS) requests, servers MUST also set, at minimum, the Access-Control-Allow-Headers: Authorization, * and Access-Control-Allow-Methods: GET headers.', function () {
        $hash = \nostriphant\Blossom\writeFile(FILES_DIRECTORY, 'Hello World!');
        $response = FeatureCase::request('OPTIONS', '/list/15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f');
        expect($response->status)->toBe('204');
        expect($response->headers['access-control-allow-origin'])->toBe('*');
        expect($response->headers['access-control-allow-headers'])->toBe('Authorization, *');
        expect(explode(', ', $response->headers['access-control-allow-methods']))->toContain('HEAD');
        expect(explode(', ', $response->headers['access-control-allow-methods']))->toContain('GET');
        expect($response->body)->toBeEmpty();
    });

    it('The header Access-Control-Max-Age: 86400 MAY be set to cache the results of a preflight request for 24 hours.', function () {
        $hash = \nostriphant\Blossom\writeFile(FILES_DIRECTORY, 'Hello World!');
        $response = FeatureCase::request('OPTIONS', '/list/15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f');
        expect($response->headers['access-control-max-age'])->toBe('86400');
    });
});

it('The GET /list/15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f endpoint MUST return the blobs uploaded by 15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f', function () {

    $files = [];
    
    for ($i = 0; $i < 100; $i++) {
        $hash = \nostriphant\Blossom\writeFile(FeatureCase::$blossom->files_directory, 'Hello World!!!' . $i, '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f');
        
        $hash_file = FeatureCase::$blossom->files_directory . '/' . $hash;
        expect($hash_file)->toBeFile();
        expect($hash_file . '.owners')->toBeDirectory();
        expect($hash_file . '.owners/15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f')->toBeFile();
        
        $files[] = $hash_file;
    }
    
    sort($files);
    
    
    $response = FeatureCase::request('GET', '/list/15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f');
    expect($response->status)->toBe('200');
    
    $blob_descriptors = json_decode($response->body);
    expect($blob_descriptors)->toHaveCount(count($files));
    
    
    $response = FeatureCase::request('GET', '/list/15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f?limit=50');
    expect($response->status)->toBe('200');
    
    $blob_descriptors = json_decode($response->body);
    expect($blob_descriptors)->toHaveCount(50);
    
    $cursor = basename($files[9]);
    
    $response = FeatureCase::request('GET', '/list/15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f?limit=50&cursor=' . $cursor);
    expect($response->status)->toBe('200');
    
    $blob_descriptors = json_decode($response->body);
    expect($blob_descriptors)->toHaveCount(50);
    
    foreach (array_slice($files, 10, 50) as $i => $expected_file) {
        expect($blob_descriptors[$i]->sha256)->toBe(basename($expected_file));
    }
    
    
    
    foreach ($files as $hash_file) {
        unlink($hash_file . '.owners/15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f');
        rmdir($hash_file . '.owners');
        unlink($hash_file);
    }
    
});

