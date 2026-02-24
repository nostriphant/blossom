<?php

namespace nostriphant\BlossomTests\Feature;

use \nostriphant\BlossomTests\FeatureCase;

    
describe("OPTIONS /upload", function() {
    it('For preflight (OPTIONS) requests, servers MUST also set, at minimum, the Access-Control-Allow-Headers: Authorization, * and Access-Control-Allow-Methods: PUT headers.', function () {
        $hash = \nostriphant\Blossom\writeFile(FILES_DIRECTORY, 'Hello World!');
        list($protocol, $status, $headers, $body) = FeatureCase::request('OPTIONS', '/upload');
        expect($status)->toBe('204');
        expect($headers['access-control-allow-origin'])->toBe('Authorization, *');
        expect(explode(', ', $headers['access-control-allow-methods']))->toContain('HEAD');
        expect(explode(', ', $headers['access-control-allow-methods']))->toContain('PUT');
        expect($body)->toBeEmpty();
    });

    it('The header Access-Control-Max-Age: 86400 MAY be set to cache the results of a preflight request for 24 hours.', function () {
        $hash = \nostriphant\Blossom\writeFile(FILES_DIRECTORY, 'Hello World!');
        list($protocol, $status, $headers, $body) = FeatureCase::request('OPTIONS', '/upload');
        expect($headers['access-control-max-age'])->toBe('86400');
    });
});

it('The PUT /upload endpoint MUST accept binary data in the body of the request', function (string $contents, string $hash) {

    $resource = tmpfile();
    fwrite($resource, $contents);
    fseek($resource, 0);
    
    $hash_file = FILES_DIRECTORY . DIRECTORY_SEPARATOR . $hash;
    
    list($protocol, $status, $headers, $body) = FeatureCase::request('PUT', '/upload', upload_resource: tmpfile(), authorization:['t' => 'upload', 'x' => $hash, 'key' => '6eeb5ad99e47115467d096e07c1c9b8b41768ab53465703f78017204adc5b0cc']);
    expect($status)->toBe('401');

    
    expect($hash_file)->not()->toBeFile();
    expect($hash_file . '.owners')->not()->toBeDirectory();
    expect($hash_file . '.owners' . DIRECTORY_SEPARATOR . '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f')->not()->toBeFile();
    
    list($protocol, $status, $headers, $body) = FeatureCase::request('PUT', '/upload', upload_resource: $resource, authorization:['t' => 'upload', 'x' => $hash]);
    expect($status)->toBe('201');

    $expected_hash = hash('sha256', 'Hello World!!!');
    $blob_descriptor = json_decode($body);
    expect($blob_descriptor->url)->toBe(FeatureCase::$blossom->url . '/' . $expected_hash);
    expect($blob_descriptor->sha256)->toBe($expected_hash);
    expect($blob_descriptor->size)->toBe(14);
    expect($blob_descriptor->type)->toBe('text/plain');
    expect($blob_descriptor->uploaded)->toBeInt();


    expect($headers['content-location'])->toBe('/' . $expected_hash);
    expect((int)$headers['content-length'])->toBe(strlen($body));
    expect($headers['access-control-allow-origin'])->toBe('*');
    
    expect($hash_file)->toBeFile();
    expect($hash_file . '.owners')->toBeDirectory();
    expect($hash_file . '.owners' . DIRECTORY_SEPARATOR . '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f')->toBeFile();
    
    unlink($hash_file . '.owners' . DIRECTORY_SEPARATOR . '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f');
    rmdir($hash_file . '.owners');
    unlink($hash_file);
})->with([
    [$contents = 'Hello World!!!', hash('sha256', $contents)]
]);


it('The PUT /upload endpoint MUST honor upload size limit', function () {
    $contents = str_repeat('aaa', 100);
    
    $resource = tmpfile();
    fwrite($resource, $contents);
    fseek($resource, 0);
    
    $hash = hash('sha256', $contents);
    $hash_file = FILES_DIRECTORY . DIRECTORY_SEPARATOR . $hash;
    
    expect($hash_file)->not()->toBeFile();
    expect($hash_file . '.owners')->not()->toBeDirectory();
    expect($hash_file . '.owners' . DIRECTORY_SEPARATOR . '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f')->not()->toBeFile();
    
    list($protocol, $status, $headers, $body) = FeatureCase::request('PUT', '/upload', upload_resource: $resource, authorization:['t' => 'upload', 'x' => $hash]);
    expect($status)->toBe('413');

    expect($hash_file)->not()->toBeFile();
    expect($hash_file . '.owners')->not()->toBeDirectory();
    expect($hash_file . '.owners' . DIRECTORY_SEPARATOR . '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f')->not()->toBeFile();
});


it('The PUT /upload endpoint MUST check content-length when existing', function () {
    $contents = str_repeat('ccc', 100);
    
    $resource = tmpfile();
    fwrite($resource, $contents);
    fseek($resource, 0);
    
    $hash = hash('sha256', $contents);
    $hash_file = FILES_DIRECTORY . DIRECTORY_SEPARATOR . $hash;
    
    expect($hash_file)->not()->toBeFile();
    expect($hash_file . '.owners')->not()->toBeDirectory();
    expect($hash_file . '.owners' . DIRECTORY_SEPARATOR . '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f')->not()->toBeFile();
    
    list($protocol, $status, $headers, $body) = FeatureCase::request('PUT', '/upload', upload_resource: $resource, authorization:['t' => 'upload', 'x' => $hash], headers:[
        'Content-Type: text/plain',
        'Content-Length: ' . strlen($contents)
    ]);
    expect($status)->toBe('413');
    expect($headers['x-reason'])->toBe('File too large. Max allowed size is 100 bytes.');

    expect($hash_file)->not()->toBeFile();
    expect($hash_file . '.owners')->not()->toBeDirectory();
    expect($hash_file . '.owners' . DIRECTORY_SEPARATOR . '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f')->not()->toBeFile();
});


it('The PUT /upload endpoint MUST check content-type when existing', function () {
    $contents = 'All right now!';
    
    $resource = tmpfile();
    fwrite($resource, $contents);
    fseek($resource, 0);
    
    $hash = hash('sha256', $contents);
    $hash_file = FILES_DIRECTORY . DIRECTORY_SEPARATOR . $hash;
    
    expect($hash_file)->not()->toBeFile();
    expect($hash_file . '.owners')->not()->toBeDirectory();
    expect($hash_file . '.owners' . DIRECTORY_SEPARATOR . '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f')->not()->toBeFile();
    
    
    list($protocol, $status, $headers, $body) = FeatureCase::request('PUT', '/upload', upload_resource: $resource, authorization:['t' => 'upload', 'x' => $hash], headers:[
        'Content-Type: audio/wav',
        'Content-Length: ' . strlen($contents)
    ]);
    expect($status)->toBe('415');
    expect($headers['x-reason'])->toBe('Unsupported file type "audio/wav".');

    expect($hash_file)->not()->toBeFile();
    expect($hash_file . '.owners')->not()->toBeDirectory();
    expect($hash_file . '.owners' . DIRECTORY_SEPARATOR . '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f')->not()->toBeFile();
});

it('Servers MUST accept DELETE requests to the /<sha256> endpoint', function () {
    $contents = 'H3llo World!!!';
    $hash = hash('sha256', $contents);
        
    $resource = tmpfile();
    fwrite($resource, $contents);
    fseek($resource, 0);
    
    $hash_file = FILES_DIRECTORY . DIRECTORY_SEPARATOR . $hash;
    
    expect($hash_file)->not()->toBeFile();
    expect($hash_file . '.owners')->not()->toBeDirectory();
    expect($hash_file . '.owners' . DIRECTORY_SEPARATOR . '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f')->not()->toBeFile();

    list($protocol, $status, $headers, $body) = FeatureCase::request('PUT', '/upload', upload_resource: $resource, authorization:['t' => 'upload', 'x' => $hash]);
    expect($status)->toBe('201');
    expect($headers['access-control-allow-origin'])->toBe('*');
    $blob_descriptor = json_decode($body);
    
    expect($hash_file)->toBeFile();
    expect($hash_file . '.owners')->toBeDirectory();
    expect($hash_file . '.owners' . DIRECTORY_SEPARATOR . '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f')->toBeFile();
    
    list($protocol, $status, $headers, $body) = FeatureCase::request('GET', '/' . $blob_descriptor->sha256, authorization:['t' => 'get', 'x' => $hash]);
    expect($status)->toBe('200');
    expect($headers['access-control-allow-origin'])->toBe('*');
    
    list($protocol, $status, $headers, $body) = FeatureCase::request('DELETE', '/' . $blob_descriptor->sha256, authorization:['t' => 'delete', 'x' => $hash, 'key' => '6eeb5ad99e47115467d096e07c1c9b8b41768ab53465703f78017204adc5b0cc']);
    expect($status)->toBe('403');
    
    list($protocol, $status, $headers, $body) = FeatureCase::request('DELETE', '/' . $blob_descriptor->sha256, authorization:['t' => 'delete', 'x' => $hash]);
    expect($status)->toBe('204');
    expect($headers['access-control-allow-origin'])->toBe('*');
    
    clearstatcache();
    expect($hash_file)->not()->toBeFile();
    expect($hash_file . '.owners' . DIRECTORY_SEPARATOR . '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f')->not()->toBeFile();
    expect(glob($hash_file . '.owners/*'))->toHaveCount(0);
    expect($hash_file . '.owners')->not()->toBeDirectory();
    
    list($protocol, $status, $headers, $body) = FeatureCase::request('GET', '/' . $blob_descriptor->sha256, authorization:['t' => 'get', 'x' => $hash]);
    expect($status)->toBe('404', $headers['x-reason'] ?? $body);
    expect($headers['access-control-allow-origin'])->toBe('*');
    
    list($protocol, $status, $headers, $body) = FeatureCase::request('DELETE', '/' . $blob_descriptor->sha256, authorization:['t' => 'delete', 'x' => $hash]);
    expect($status)->toBe('200');
    expect($headers['access-control-allow-origin'])->toBe('*');
    
});