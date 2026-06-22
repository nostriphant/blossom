<?php

namespace nostriphant\BlossomTests\Feature;

use \nostriphant\BlossomTests\FeatureCase;

    
describe("OPTIONS /<sha-256>", function() {
    it('For preflight (OPTIONS) requests, servers MUST also set, at minimum, the Access-Control-Allow-Headers: Authorization, * and Access-Control-Allow-Methods: GET, HEAD headers.', function () {
        $hash = \nostriphant\Blossom\writeFile(FeatureCase::$blossom->files_directory, 'Hello World!');
        $response = FeatureCase::request('OPTIONS', '/' . $hash);
        expect($response->status)->toBe('204');
        expect($response->headers['access-control-allow-origin'])->toBe('*');
        expect($response->headers['access-control-allow-headers'])->toBe('Authorization, *');
        expect(explode(', ', $response->headers['access-control-allow-methods']))->toContain('GET', 'HEAD');
        expect($response->body)->toBeEmpty();
        
        \nostriphant\Blossom\deleteFile(FeatureCase::$blossom->files_directory, $hash);
    });

    it('Allow for extensions', function () {
        $hash = \nostriphant\Blossom\writeFile(FeatureCase::$blossom->files_directory, 'Hello World!');
        $response = FeatureCase::request('OPTIONS', '/' . $hash . '.txt');
        expect($response->status)->toBe('204');
        expect($response->headers['access-control-allow-origin'])->toBe('*');
        expect($response->headers['access-control-allow-headers'])->toBe('Authorization, *');
        expect(explode(', ', $response->headers['access-control-allow-methods']))->toContain('GET', 'HEAD');
        expect($response->body)->toBeEmpty();
        
        \nostriphant\Blossom\deleteFile(FeatureCase::$blossom->files_directory, $hash);
    });

    it('The header Access-Control-Max-Age: 86400 MAY be set to cache the results of a preflight request for 24 hours.', function () {
        $hash = \nostriphant\Blossom\writeFile(FeatureCase::$blossom->files_directory, 'Hello World!');
        $response = FeatureCase::request('OPTIONS', '/' . $hash);
        expect($response->headers['access-control-max-age'])->toBe('86400');
        
        \nostriphant\Blossom\deleteFile(FeatureCase::$blossom->files_directory, $hash);
    });
});


it('GET|HEAD / gives 200 OK', function () {
    $response = FeatureCase::request('HEAD', '/');
    expect($response->status)->toBe('200');
    expect($response->body)->toBeEmpty();
    
    $response = FeatureCase::request('GET', '/');
    expect($response->status)->toBe('200');
    expect($response->body)->toBeEmpty();
});


it('GET /<sha-256> without authorization', function () {
    $hash = \nostriphant\Blossom\writeFile(FeatureCase::$blossom->files_directory, 'Hello World!');
    $response = FeatureCase::request('GET', '/' . $hash);
    expect($response->status)->toBe('200');
    expect($response->headers['content-type'])->toContain('text/plain');
    expect($response->headers['access-control-allow-origin'])->toBe('*');
    expect($response->body)->toBe('Hello World!');
    \nostriphant\Blossom\deleteFile(FeatureCase::$blossom->files_directory, $hash);
});


it('GET /<sha-256> returns proper content-type', function () {
    $im = @\imagecreate(110, 20)
        or die("Cannot Initialize new GD image stream");
    $background_color = \imagecolorallocate($im, 0, 0, 0);
    $text_color = \imagecolorallocate($im, 233, 14, 91);
    \imagestring($im, 1, 5, 5,  "A Simple Text String", $text_color);
    $directory = FeatureCase::$blossom->files_directory;
    
    $tempfile = tempnam($directory, 'tmp.');
    \imagepng($im, $tempfile);
    $hash = hash_file('sha256', $tempfile);
    rename($tempfile, $directory . DIRECTORY_SEPARATOR . $hash);

    $response = FeatureCase::request('GET', '/' . $hash);
    expect($response->status)->toBe('200');
    expect($response->headers['content-type'])->toContain('image/png');
    expect($response->headers['access-control-allow-origin'])->toBe('*');
    expect($response->body)->toBe(file_get_contents($directory . DIRECTORY_SEPARATOR . $hash));
    
    \nostriphant\Blossom\deleteFile(FeatureCase::$blossom->files_directory, $hash);
});


it('GET /<sha-256>', function () {
    $hash = \nostriphant\Blossom\writeFile(FeatureCase::$blossom->files_directory, 'Hello World!');
    $response = FeatureCase::request('GET', '/' . $hash, authorization: ['t' => 'get', 'x' => $hash]);
    expect($response->status)->toBe('200');
    expect($response->headers['content-type'])->toContain('text/plain');
    expect($response->headers['access-control-allow-origin'])->toBe('*');
    expect($response->body)->toBe('Hello World!');
    \nostriphant\Blossom\deleteFile(FeatureCase::$blossom->files_directory, $hash);
});

it('GET /<sha-256>.txt', function () {
    $hash = \nostriphant\Blossom\writeFile(FeatureCase::$blossom->files_directory, 'Hello World!');
    $response = FeatureCase::request('GET', '/' . $hash . '.txt', authorization: ['t' => 'get', 'x' => $hash]);
    expect($response->status)->toBe('200');
    expect($response->headers['content-type'])->toContain('text/plain');
    expect($response->headers['access-control-allow-origin'])->toBe('*');
    expect($response->body)->toBe('Hello World!');
    \nostriphant\Blossom\deleteFile(FeatureCase::$blossom->files_directory, $hash);
});

it('HEAD /<sha-256>', function () {
    $hash = \nostriphant\Blossom\writeFile(FeatureCase::$blossom->files_directory, 'Hello World!');
    $response = FeatureCase::request('HEAD', '/' . $hash, authorization: ['t' => 'get', 'x' => $hash]);
    expect($response->status)->toBe('200');
    expect($response->headers['content-type'])->toContain('text/plain');
    expect($response->headers['access-control-allow-origin'])->toBe('*');
    expect($response->body)->toBeEmpty();
    \nostriphant\Blossom\deleteFile(FeatureCase::$blossom->files_directory, $hash);
});

it('HEAD /<sha-256>.txt', function () {
    $hash = \nostriphant\Blossom\writeFile(FeatureCase::$blossom->files_directory, 'Hello World!');
    $response = FeatureCase::request('HEAD', '/' . $hash . '.txt', authorization: ['t' => 'get', 'x' => $hash]);
    expect($response->status)->toBe('200');
    expect($response->headers['content-type'])->toContain('text/plain');
    expect($response->headers['access-control-allow-origin'])->toBe('*');
    expect($response->body)->toBeEmpty();
    \nostriphant\Blossom\deleteFile(FeatureCase::$blossom->files_directory, $hash);
});

it('responds with 404 when file missing', function () {
    $response = FeatureCase::request('GET', '/not-existing', authorization: ['t' => 'get', 'x' => 'not-existing']);
    expect($response->status)->toBe('404');
    expect($response->headers['content-type'])->toContain('text/html');
});