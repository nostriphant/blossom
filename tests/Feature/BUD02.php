<?php

namespace nostriphant\BlossomTests\Feature;

use \nostriphant\BlossomTests\FeatureCase;

    
describe("OPTIONS /upload", function() {
    it('For preflight (OPTIONS) requests, servers MUST also set, at minimum, the Access-Control-Allow-Headers: Authorization, * and Access-Control-Allow-Methods: PUT headers.', function () {
        $hash = FeatureCase::writeFile('Hello World!');
        list($protocol, $status, $headers, $body) = FeatureCase::request('OPTIONS', '/upload');
        expect($status)->toBe('204');
        expect($headers['access-control-allow-origin'])->toBe('Authorization, *');
        expect(explode(', ', $headers['access-control-allow-methods']))->toContain('PUT');
        expect($body)->toBeEmpty();
    });

    it('The header Access-Control-Max-Age: 86400 MAY be set to cache the results of a preflight request for 24 hours.', function () {
        $hash = FeatureCase::writeFile('Hello World!');
        list($protocol, $status, $headers, $body) = FeatureCase::request('OPTIONS', '/upload');
        expect($headers['access-control-max-age'])->toBe('86400');
    });
});

it('The PUT /upload endpoint MUST accept binary data in the body of the request', function () {

    $resource = tmpfile();
    fwrite($resource, 'Hello World!!!');
    fseek($resource, 0);

    list($protocol, $status, $headers, $body) = FeatureCase::request('PUT', '/upload', upload_resource: $resource);
    expect($status)->toBe('201');

    $expected_hash = hash('sha256', 'Hello World!!!');
    $blob_descriptor = json_decode($body);
    expect($blob_descriptor->url)->toBe(FeatureCase::RELAY_URL . '/' . $expected_hash);
    expect($blob_descriptor->sha256)->toBe($expected_hash);
    expect($blob_descriptor->size)->toBe(14);
    expect($blob_descriptor->type)->toBe('text/plain');
    expect($blob_descriptor->uploaded)->toBeInt();


    expect($headers['content-location'])->toBe('/' . $expected_hash);
});


it('Servers MUST accept DELETE requests to the /<sha256> endpoint', function () {

    $resource = tmpfile();
    fwrite($resource, 'Hello World!!!');
    fseek($resource, 0);

    list($protocol, $status, $headers, $body) = FeatureCase::request('PUT', '/upload', upload_resource: $resource);
    expect($status)->toBe('201');
    $blob_descriptor = json_decode($body);
    
    list($protocol, $status, $headers, $body) = FeatureCase::request('GET', '/' . $blob_descriptor->sha256);
    expect($status)->toBe('200');
    
    list($protocol, $status, $headers, $body) = FeatureCase::request('DELETE', '/' . $blob_descriptor->sha256);
    expect($status)->toBe('204');
    
    list($protocol, $status, $headers, $body) = FeatureCase::request('GET', '/' . $blob_descriptor->sha256);
    expect($status)->toBe('404');
    
    list($protocol, $status, $headers, $body) = FeatureCase::request('DELETE', '/' . $blob_descriptor->sha256);
    expect($status)->toBe('200');
});