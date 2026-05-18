<?php

namespace nostriphant\BlossomTests\Feature;

use \nostriphant\BlossomTests\FeatureCase;

    
describe("OPTIONS /<sha-256>", function() {
    it('For preflight (OPTIONS) requests, servers MUST also set, at minimum, the Access-Control-Allow-Headers: Authorization, * and Access-Control-Allow-Methods: GET, HEAD headers.', function () {
        $hash = \nostriphant\Blossom\writeFile(FeatureCase::$blossom->files_directory, 'Hello World!');
        list($protocol, $status, $headers, $body) = FeatureCase::request('OPTIONS', '/' . $hash);
        expect($status)->toBe('204');
        expect($headers['access-control-allow-origin'])->toBe('Authorization, *');
        expect(explode(', ', $headers['access-control-allow-methods']))->toContain('GET', 'HEAD');
        expect($body)->toBeEmpty();
        
        \nostriphant\Blossom\deleteFile(FeatureCase::$blossom->files_directory, $hash);
    });

    it('Allow for extensions', function () {
        $hash = \nostriphant\Blossom\writeFile(FeatureCase::$blossom->files_directory, 'Hello World!');
        list($protocol, $status, $headers, $body) = FeatureCase::request('OPTIONS', '/' . $hash . '.txt');
        expect($status)->toBe('204');
        expect($headers['access-control-allow-origin'])->toBe('Authorization, *');
        expect(explode(', ', $headers['access-control-allow-methods']))->toContain('GET', 'HEAD');
        expect($body)->toBeEmpty();
        
        \nostriphant\Blossom\deleteFile(FeatureCase::$blossom->files_directory, $hash);
    });

    it('The header Access-Control-Max-Age: 86400 MAY be set to cache the results of a preflight request for 24 hours.', function () {
        $hash = \nostriphant\Blossom\writeFile(FeatureCase::$blossom->files_directory, 'Hello World!');
        list($protocol, $status, $headers, $body) = FeatureCase::request('OPTIONS', '/' . $hash);
        expect($headers['access-control-max-age'])->toBe('86400');
        
        \nostriphant\Blossom\deleteFile(FeatureCase::$blossom->files_directory, $hash);
    });
});


it('GET /<sha-256> without authorization', function () {
    $hash = \nostriphant\Blossom\writeFile(FeatureCase::$blossom->files_directory, 'Hello World!');
    list($protocol, $status, $headers, $body) = FeatureCase::request('GET', '/' . $hash);
    expect($status)->toBe('200');
    expect($headers['content-type'])->toContain('text/plain');
    expect($headers['access-control-allow-origin'])->toBe('*');
    expect($body)->toBe('Hello World!');
    \nostriphant\Blossom\deleteFile(FeatureCase::$blossom->files_directory, $hash);
});

it('GET /<sha-256>', function () {
    $hash = \nostriphant\Blossom\writeFile(FeatureCase::$blossom->files_directory, 'Hello World!');
    list($protocol, $status, $headers, $body) = FeatureCase::request('GET', '/' . $hash, authorization: ['t' => 'get', 'x' => $hash]);
    expect($status)->toBe('200');
    expect($headers['content-type'])->toContain('text/plain');
    expect($headers['access-control-allow-origin'])->toBe('*');
    expect($body)->toBe('Hello World!');
    \nostriphant\Blossom\deleteFile(FeatureCase::$blossom->files_directory, $hash);
});

it('GET /<sha-256>.txt', function () {
    $hash = \nostriphant\Blossom\writeFile(FeatureCase::$blossom->files_directory, 'Hello World!');
    list($protocol, $status, $headers, $body) = FeatureCase::request('GET', '/' . $hash . '.txt', authorization: ['t' => 'get', 'x' => $hash]);
    expect($status)->toBe('200');
    expect($headers['content-type'])->toContain('text/plain');
    expect($headers['access-control-allow-origin'])->toBe('*');
    expect($body)->toBe('Hello World!');
    \nostriphant\Blossom\deleteFile(FeatureCase::$blossom->files_directory, $hash);
});

it('HEAD /<sha-256>', function () {
    $hash = \nostriphant\Blossom\writeFile(FeatureCase::$blossom->files_directory, 'Hello World!');
    list($protocol, $status, $headers, $body) = FeatureCase::request('HEAD', '/' . $hash, authorization: ['t' => 'get', 'x' => $hash]);
    expect($status)->toBe('200');
    expect($headers['content-type'])->toContain('text/plain');
    expect($headers['access-control-allow-origin'])->toBe('*');
    expect($body)->toBeEmpty();
    \nostriphant\Blossom\deleteFile(FeatureCase::$blossom->files_directory, $hash);
});

it('HEAD /<sha-256>.txt', function () {
    $hash = \nostriphant\Blossom\writeFile(FeatureCase::$blossom->files_directory, 'Hello World!');
    list($protocol, $status, $headers, $body) = FeatureCase::request('HEAD', '/' . $hash . '.txt', authorization: ['t' => 'get', 'x' => $hash]);
    expect($status)->toBe('200');
    expect($headers['content-type'])->toContain('text/plain');
    expect($headers['access-control-allow-origin'])->toBe('*');
    expect($body)->toBeEmpty();
    \nostriphant\Blossom\deleteFile(FeatureCase::$blossom->files_directory, $hash);
});

it('responds with 404 when file missing', function () {
    list($protocol, $status, $headers, $body) = FeatureCase::request('GET', '/not-existing', authorization: ['t' => 'get', 'x' => 'not-existing']);
    expect($status)->toBe('404');
    expect($headers['content-type'])->toContain('text/html');
});