<?php

namespace nostriphant\BlossomTests\Feature;

use \nostriphant\BlossomTests\FeatureCase;

    
describe("OPTIONS /mirror", function() {
    it('For preflight (OPTIONS) requests, servers MUST also set, at minimum, the Access-Control-Allow-Headers: Authorization, * and Access-Control-Allow-Methods: PUT headers.', function () {
        $hash = \nostriphant\Blossom\writeFile(FILES_DIRECTORY, 'Hello World!');
        list($protocol, $status, $headers, $body) = FeatureCase::request('OPTIONS', '/upload');
        expect($status)->toBe('204');
        expect($headers['access-control-allow-origin'])->toBe('Authorization, *');
        expect(explode(', ', $headers['access-control-allow-methods']))->toContain('PUT');
        expect($body)->toBeEmpty();
        
        \nostriphant\Blossom\deleteFile(FILES_DIRECTORY, $hash);
    });

    it('The header Access-Control-Max-Age: 86400 MAY be set to cache the results of a preflight request for 24 hours.', function () {
        $hash = \nostriphant\Blossom\writeFile(FILES_DIRECTORY, 'Hello World!');
        list($protocol, $status, $headers, $body) = FeatureCase::request('OPTIONS', '/upload');
        expect($headers['access-control-max-age'])->toBe('86400');
        \nostriphant\Blossom\deleteFile(FILES_DIRECTORY, $hash);
    });
});

it('The /mirror endpoint MUST download the blob from the specified URL and verify that there is at least one x tag in the authorization event matching the sha256 hash of the download blob', function () {

    $blossom = FeatureCase::start_blossom('127.0.0.1:8088', ROOT_DIR . "/logs/blossom-8088.log", ROOT_DIR . "/logs/blossom-errors-8088.log");
    
    try {
        $contents = 'Hello Wddorld!!!';
        
        $resource = tmpfile();
        fwrite($resource, $contents);
        fseek($resource, 0);

        $expected_hash = hash('sha256', $contents);
        $hash_file = $blossom->files_directory . DIRECTORY_SEPARATOR . $expected_hash;

        list($protocol, $status, $headers, $body) = FeatureCase::request('PUT', '/upload', upload_resource: $resource, authorization:['t' => 'upload', 'x' => $expected_hash]);
        expect($status)->toBe('201');

        $blob_descriptor = json_decode($body);
        expect($blob_descriptor->url)->toBe(FeatureCase::$blossom->url . '/' . $expected_hash);
        expect($blob_descriptor->sha256)->toBe($expected_hash);
        expect($blob_descriptor->size)->toBe(strlen($contents), $expected_hash);
        expect($blob_descriptor->type)->toBe('text/plain');
        expect($blob_descriptor->uploaded)->toBeInt();

        $mirror_content = '{"url": "'.$blob_descriptor->url.'"}';
        list($protocol, $status, $headers, $body) = FeatureCase::request('PUT', 'http://127.0.0.1:8088/mirror', upload_resource: $mirror_content, authorization:['t' => 'upload', 'x' => $expected_hash]);
        expect($status)->toBe('201', $headers['x-reason'] ?? '');
        
        $blob_descriptor = json_decode($body);
        expect($blob_descriptor->url)->toBe(FeatureCase::$blossom->url . '/' . $expected_hash);
        expect($blob_descriptor->sha256)->toBe($expected_hash);
        expect($blob_descriptor->size)->toBe(strlen($contents), $expected_hash);
        expect($blob_descriptor->type)->toBe('text/plain');
        expect($blob_descriptor->uploaded)->toBeInt();


        expect($headers['content-location'])->toBe('/' . $expected_hash);
        expect((int)$headers['content-length'])->toBe(strlen($body));
        expect($headers['access-control-allow-origin'])->toBe('*');

        expect($hash_file)->toBeFile();
        expect($hash_file . '.owners')->toBeDirectory();
        expect($hash_file . '.owners' . DIRECTORY_SEPARATOR . '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f')->toBeFile();

        list($protocol, $status, $headers, $body) = FeatureCase::request('DELETE', 'http://127.0.0.1:8088/' . $blob_descriptor->sha256, authorization:['t' => 'delete', 'x' => $expected_hash]);
        expect($status)->toBe('204');
        expect($headers['access-control-allow-origin'])->toBe('*');

        clearstatcache();
        expect($hash_file)->not()->toBeFile();
        expect($hash_file . '.owners' . DIRECTORY_SEPARATOR . '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f')->not()->toBeFile();
        expect(glob($hash_file . '.owners/*'))->toHaveCount(0);
        expect($hash_file . '.owners')->not()->toBeDirectory();
    } catch (\Exception $e) {
        $blossom(false);
        throw $e;
    }
    
    
    
    try {
        $contents = str_repeat('bbb', 100);
        $hash ??= hash('sha256', $contents);
        $content_length ??= strlen($contents);

        $hash_original_file = FILES_DIRECTORY . DIRECTORY_SEPARATOR . $hash;
        file_put_contents($hash_original_file, $contents);
        is_dir($hash_original_file . '.owners') || mkdir($hash_original_file . '.owners');
        touch($hash_original_file . '.owners' . DIRECTORY_SEPARATOR . '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f');

        $mirror_content = '{"url": "'.FeatureCase::$blossom->url . '/' . $hash.'"}';
        list($protocol, $status, $headers, $body) = FeatureCase::request('PUT', 'http://127.0.0.1:8088/mirror', upload_resource: $mirror_content, authorization:['t' => 'upload', 'x' => $hash]);
        expect($status)->toBe('413', $body);
        expect($headers['x-reason'])->toBe('Filesize larger than max allowed file size.');

        clearstatcache();
        $hash_file = $blossom->files_directory . '/' . $hash;
        expect($hash_file)->not()->toBeFile();
        expect($hash_file . '.owners' . DIRECTORY_SEPARATOR . '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f')->not()->toBeFile();
        expect(glob($hash_file . '.owners/*'))->toHaveCount(0);
        expect($hash_file . '.owners')->not()->toBeDirectory();
    } catch (\Exception $e) {
        $blossom(false);
        throw $e;
    }
    
    
    try {
        $contents = str_repeat('ddd', 20);
        $hash ??= hash('sha256', $contents);
        $content_length ??= strlen($contents);

        $hash_original_file = FILES_DIRECTORY . DIRECTORY_SEPARATOR . $hash;
        file_put_contents($hash_original_file, $contents);
        is_dir($hash_original_file . '.owners') || mkdir($hash_original_file . '.owners');
        touch($hash_original_file . '.owners' . DIRECTORY_SEPARATOR . '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f');

        $mirror_content = '{"url": "'.FeatureCase::$blossom->url . '/' . $hash.'"}';
        list($protocol, $status, $headers, $body) = FeatureCase::request('PUT', 'http://127.0.0.1:8088/mirror', upload_resource: $mirror_content, authorization:['t' => 'upload', 'x' => $expected_hash]);
        expect($status)->toBe('403', $body);
        expect($headers['x-reason'])->toBe('Authorized hash (dccc1450d6fc4232955fcc5cf81105d874c4c6f8c710a71b2763d2c3238e923f)  does not match hash of contents (29f662e3fded284e2695546ef01ede7d4d01f9d28b706d41b65b99ad600154d3).');

        clearstatcache();
        $hash_file = $blossom->files_directory . '/' . $hash;
        expect($hash_file)->not()->toBeFile();
        expect($hash_file . '.owners' . DIRECTORY_SEPARATOR . '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f')->not()->toBeFile();
        expect(glob($hash_file . '.owners/*'))->toHaveCount(0);
        expect($hash_file . '.owners')->not()->toBeDirectory();
    } catch (\Exception $e) {
        $blossom(false);
        throw $e;
    }
    
    $blossom();
});


//
//
//it('The PUT /upload endpoint MUST check content-type when existing', function () {
//    $contents = str_repeat('ccc', 100);
//    
//    $resource = tmpfile();
//    fwrite($resource, $contents);
//    fseek($resource, 0);
//    
//    $hash = hash('sha256', $contents);
//    $hash_file = FILES_DIRECTORY . DIRECTORY_SEPARATOR . $hash;
//    
//    expect($hash_file)->not()->toBeFile();
//    expect($hash_file . '.owners')->not()->toBeDirectory();
//    expect($hash_file . '.owners' . DIRECTORY_SEPARATOR . '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f')->not()->toBeFile();
//    
//    list($protocol, $status, $headers, $body) = FeatureCase::request('PUT', '/upload', upload_resource: $resource, authorization:['t' => 'upload', 'x' => $hash], headers:[
//        'Content-Type: text/plain',
//        'Content-Length: ' . strlen($contents)
//    ]);
//    expect($status)->toBe('413');
//    expect($headers['x-reason'])->toBe('File too large. Max allowed size is 100 bytes.');
//
//    expect($hash_file)->not()->toBeFile();
//    expect($hash_file . '.owners')->not()->toBeDirectory();
//    expect($hash_file . '.owners' . DIRECTORY_SEPARATOR . '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f')->not()->toBeFile();
//});
//
//
//it('The PUT /upload endpoint MUST check content-length when existing', function () {
//    $contents = 'All right now!';
//    
//    $resource = tmpfile();
//    fwrite($resource, $contents);
//    fseek($resource, 0);
//    
//    $hash = hash('sha256', $contents);
//    $hash_file = FILES_DIRECTORY . DIRECTORY_SEPARATOR . $hash;
//    
//    expect($hash_file)->not()->toBeFile();
//    expect($hash_file . '.owners')->not()->toBeDirectory();
//    expect($hash_file . '.owners' . DIRECTORY_SEPARATOR . '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f')->not()->toBeFile();
//    
//    
//    list($protocol, $status, $headers, $body) = FeatureCase::request('PUT', '/upload', upload_resource: $resource, authorization:['t' => 'upload', 'x' => $hash], headers:[
//        'Content-Type: audio/wav',
//        'Content-Length: ' . strlen($contents)
//    ]);
//    expect($status)->toBe('415');
//    expect($headers['x-reason'])->toBe('Unsupported file type.');
//
//    expect($hash_file)->not()->toBeFile();
//    expect($hash_file . '.owners')->not()->toBeDirectory();
//    expect($hash_file . '.owners' . DIRECTORY_SEPARATOR . '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f')->not()->toBeFile();
//});
