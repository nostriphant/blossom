<?php

namespace nostriphant\BlossomTests\Feature;

use \nostriphant\BlossomTests\FeatureCase;

beforeAll(function() {
    expect(\nostriphant\BlossomTests\make_files_directory())->toBeTrue();
    expect(\nostriphant\BlossomTests\files_directory())->toBeDirectory();

    FeatureCase::relay_process();
});

describe("BUD-01", function() {
    it('GET /<sha-256>', function () {
        $hash = FeatureCase::writeFile('Hello World!');
        list($protocol, $code, $headers, $body) = FeatureCase::request('GET', '/' . $hash);
        expect($code)->toBe('200');
        expect($headers['content-type'])->toContain('text/plain');
        expect($body)->toBe('Hello World!');
    });

    it('HEAD /<sha-256>', function () {
        $hash = FeatureCase::writeFile('Hello World!');
        list($protocol, $code, $headers, $body) = FeatureCase::request('HEAD', '/' . $hash);
        expect($code)->toBe('200');
        expect($headers['content-type'])->toContain('text/plain');
        expect($body)->toBeEmpty();
    });
    

    it('responds with 404 when file missing', function () {
        list($protocol, $code, $headers, $body) = FeatureCase::request('GET', '/not-existing');
        expect($code)->toBe('404');
        expect($headers['content-type'])->toContain('text/html');
    });
    
});

afterAll(function() {
    FeatureCase::end_relay_process();

    \nostriphant\RelayTests\destroy_files_directory();
});