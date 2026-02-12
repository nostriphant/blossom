<?php

namespace nostriphant\BlossomTests\Feature;

use \nostriphant\BlossomTests\FeatureCase;

beforeAll(function() {
    expect(\nostriphant\BlossomTests\make_files_directory())->toBeTrue();
    expect(\nostriphant\BlossomTests\files_directory())->toBeDirectory();

    FeatureCase::relay_process();
});

describe("BUD-01", function() {
    
    describe("OPTIONS /<sha-256>", function() {
        it('For preflight (OPTIONS) requests, servers MUST also set, at minimum, the Access-Control-Allow-Headers: Authorization, * and Access-Control-Allow-Methods: GET, HEAD headers.', function () {
            $hash = FeatureCase::writeFile('Hello World!');
            list($protocol, $status, $headers, $body) = FeatureCase::request('OPTIONS', '/' . $hash);
            expect($status)->toBe('204');
            expect($headers['access-control-allow-origin'])->toBe('Authorization, *');
            expect(explode(', ', $headers['access-control-allow-methods']))->toContain('GET', 'HEAD');
            expect($body)->toBeEmpty();
        });
        
        it('Allow for extensions', function () {
            $hash = FeatureCase::writeFile('Hello World!');
            list($protocol, $status, $headers, $body) = FeatureCase::request('OPTIONS', '/' . $hash . '.txt');
            expect($status)->toBe('204');
            expect($headers['access-control-allow-origin'])->toBe('Authorization, *');
            expect(explode(', ', $headers['access-control-allow-methods']))->toContain('GET', 'HEAD');
            expect($body)->toBeEmpty();
        });

        it('The header Access-Control-Max-Age: 86400 MAY be set to cache the results of a preflight request for 24 hours.', function () {
            $hash = FeatureCase::writeFile('Hello World!');
            list($protocol, $status, $headers, $body) = FeatureCase::request('OPTIONS', '/' . $hash);
            expect($headers['access-control-max-age'])->toBe('86400');
        });
    });
    
    it('GET /<sha-256>', function () {
        $hash = FeatureCase::writeFile('Hello World!');
        list($protocol, $status, $headers, $body) = FeatureCase::request('GET', '/' . $hash);
        expect($status)->toBe('200');
        expect($headers['content-type'])->toContain('text/plain');
        expect($headers['access-control-allow-origin'])->toBe('*');
        expect($body)->toBe('Hello World!');
    });

    it('GET /<sha-256>.txt', function () {
        $hash = FeatureCase::writeFile('Hello World!');
        list($protocol, $status, $headers, $body) = FeatureCase::request('GET', '/' . $hash . '.txt');
        expect($status)->toBe('200');
        expect($headers['content-type'])->toContain('text/plain');
        expect($headers['access-control-allow-origin'])->toBe('*');
        expect($body)->toBe('Hello World!');
    });
    
    it('HEAD /<sha-256>', function () {
        $hash = FeatureCase::writeFile('Hello World!');
        list($protocol, $status, $headers, $body) = FeatureCase::request('HEAD', '/' . $hash);
        expect($status)->toBe('200');
        expect($headers['content-type'])->toContain('text/plain');
        expect($headers['access-control-allow-origin'])->toBe('*');
        expect($body)->toBeEmpty();
    });
    
    it('HEAD /<sha-256>.txt', function () {
        $hash = FeatureCase::writeFile('Hello World!');
        list($protocol, $status, $headers, $body) = FeatureCase::request('HEAD', '/' . $hash . '.txt');
        expect($status)->toBe('200');
        expect($headers['content-type'])->toContain('text/plain');
        expect($headers['access-control-allow-origin'])->toBe('*');
        expect($body)->toBeEmpty();
    });

    it('responds with 404 when file missing', function () {
        list($protocol, $status, $headers, $body) = FeatureCase::request('GET', '/not-existing');
        expect($status)->toBe('404');
        expect($headers['content-type'])->toContain('text/html');
    });
    
});


describe("BUD-02", function() {
    
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
    
    it('PUT /upload', function () {
        
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
});

afterAll(function() {
    FeatureCase::end_relay_process();
    \nostriphant\RelayTests\destroy_files_directory();
});