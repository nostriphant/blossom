<?php

namespace nostriphant\BlossomTests\Feature;

use \nostriphant\BlossomTests\FeatureCase;

beforeAll(function() {
    expect(\nostriphant\BlossomTests\make_files_directory())->toBeTrue();
    expect(\nostriphant\BlossomTests\files_directory())->toBeDirectory();

    FeatureCase::relay_process();
});

describe("BUD-01", function() {
    
    describe("Preflight", function() {
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

afterAll(function() {
    FeatureCase::end_relay_process();
    \nostriphant\RelayTests\destroy_files_directory();
});