<?php

namespace nostriphant\BlossomTests\Feature;

use \nostriphant\BlossomTests\FeatureCase;

    
describe("OPTIONS /upload", function() {
    it('For preflight (OPTIONS) requests, servers MUST also set, at minimum, the Access-Control-Allow-Headers: Authorization, * and Access-Control-Allow-Methods: PUT headers.', function () {
        $hash = \nostriphant\Blossom\writeFile(FILES_DIRECTORY, 'Hello World!');
        list($protocol, $status, $headers, $body) = FeatureCase::request('OPTIONS', '/upload');
        expect($status)->toBe('204');
        expect($headers['access-control-allow-origin'])->toBe('Authorization, *');
        expect(explode(', ', $headers['access-control-allow-methods']))->toContain('PUT');
        expect($body)->toBeEmpty();
    });

    it('The header Access-Control-Max-Age: 86400 MAY be set to cache the results of a preflight request for 24 hours.', function () {
        $hash = \nostriphant\Blossom\writeFile(FILES_DIRECTORY, 'Hello World!');
        list($protocol, $status, $headers, $body) = FeatureCase::request('OPTIONS', '/upload');
        expect($headers['access-control-max-age'])->toBe('86400');
    });
});

it('The HEAD /upload endpoint MUST use the X-SHA-256, X-Content-Type and X-Content-Length headers required to be sent by client', function () {
    $contents = 'shoebydoe';
    $hash ??= hash('sha256', $contents);
    $content_length ??= strlen($contents);
    
    $resource = tmpfile();
    fwrite($resource, $contents);
    fseek($resource, 0);
    
    $hash_file = FILES_DIRECTORY . DIRECTORY_SEPARATOR . $hash;
    
    list($protocol, $status, $headers, $body) = FeatureCase::request('HEAD', '/upload', upload_resource: tmpfile(), authorization:['t' => 'upload', 'x' => $hash, 'key' => '6eeb5ad99e47115467d096e07c1c9b8b41768ab53465703f78017204adc5b0cc']);
    expect($status)->toBe('400');
});

it('The HEAD /upload endpoint MUST use the X-SHA-256, X-Content-Type and X-Content-Length headers sent by client', function (string $contents, ?string $hash, ?int $content_length, string $response_status, ?string $x_reason = null) {
    $hash ??= hash('sha256', $contents);
    $content_length ??= strlen($contents);
    
    $resource = tmpfile();
    fwrite($resource, $contents);
    fseek($resource, 0);
    
    $hash_file = FILES_DIRECTORY . DIRECTORY_SEPARATOR . $hash;
    
    expect($hash_file)->not()->toBeFile();
    expect($hash_file . '.owners')->not()->toBeDirectory();
    expect($hash_file . '.owners' . DIRECTORY_SEPARATOR . '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f')->not()->toBeFile();
    
    list($protocol, $status, $headers, $body) = FeatureCase::request('HEAD', '/upload', upload_resource: $resource, authorization:['t' => 'upload', 'x' => $hash], headers: [
        'X-Content-Type: text/plain',
        'X-Content-Length: ' . $content_length,
        'X-SHA-256: ' . $hash
    ]);
    expect($status)->toBe($response_status);
    if (isset($x_reason)) {
        expect($headers['x-reason'])->toBe($x_reason);
    } else {
        expect($headers)->not()->toHaveKey('x-reason');
    }
})->with([
    ['Heldlo World!!!', null, null, '200'],
    ['Heldlo World!!!', null, 1024 ^ 8, '413', 'File too large. Max allowed size is 100 bytes.']
]);
