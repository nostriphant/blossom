<?php

namespace nostriphant\BlossomTests\Feature;

use \nostriphant\BlossomTests\FeatureCase;

    
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

it('GET /<sha-256> without authorizations fails with 401', function () {
    $hash = FeatureCase::writeFile('Hello World!');
    list($protocol, $status, $headers, $body) = FeatureCase::request('GET', '/' . $hash);
    expect($status)->toBe('401');
    list($protocol, $status, $headers, $body) = FeatureCase::request('GET', '/' . $hash, authorization: ['x' => $hash]);
    expect($status)->toBe('401');
    list($protocol, $status, $headers, $body) = FeatureCase::request('GET', '/' . $hash, authorization: ['t' => 'get']);
    expect($status)->toBe('401');
    list($protocol, $status, $headers, $body) = FeatureCase::request('GET', '/' . $hash, authorization: ['t' => 'get', 'x' => $hash, 'expiration' => time() - 3600]);
    expect($status)->toBe('401');
});

it('GET /<sha-256>', function () {
    $hash = FeatureCase::writeFile('Hello World!');
    list($protocol, $status, $headers, $body) = FeatureCase::request('GET', '/' . $hash, authorization: ['t' => 'get', 'x' => $hash]);
    expect($status)->toBe('200');
    expect($headers['content-type'])->toContain('text/plain');
    expect($headers['access-control-allow-origin'])->toBe('*');
    expect($body)->toBe('Hello World!');
});

it('GET /<sha-256>.txt', function () {
    $hash = FeatureCase::writeFile('Hello World!');
    list($protocol, $status, $headers, $body) = FeatureCase::request('GET', '/' . $hash . '.txt', authorization: ['t' => 'get', 'x' => $hash]);
    expect($status)->toBe('200');
    expect($headers['content-type'])->toContain('text/plain');
    expect($headers['access-control-allow-origin'])->toBe('*');
    expect($body)->toBe('Hello World!');
});

it('HEAD /<sha-256>', function () {
    $hash = FeatureCase::writeFile('Hello World!');
    list($protocol, $status, $headers, $body) = FeatureCase::request('HEAD', '/' . $hash, authorization: ['t' => 'get', 'x' => $hash]);
    expect($status)->toBe('200');
    expect($headers['content-type'])->toContain('text/plain');
    expect($headers['access-control-allow-origin'])->toBe('*');
    expect($body)->toBeEmpty();
});

it('HEAD /<sha-256>.txt', function () {
    $hash = FeatureCase::writeFile('Hello World!');
    list($protocol, $status, $headers, $body) = FeatureCase::request('HEAD', '/' . $hash . '.txt', authorization: ['t' => 'get', 'x' => $hash]);
    expect($status)->toBe('200');
    expect($headers['content-type'])->toContain('text/plain');
    expect($headers['access-control-allow-origin'])->toBe('*');
    expect($body)->toBeEmpty();
});

it('responds with 404 when file missing', function () {
    list($protocol, $status, $headers, $body) = FeatureCase::request('GET', '/not-existing', authorization: ['t' => 'get', 'x' => 'not-existing']);
    expect($status)->toBe('404');
    expect($headers['content-type'])->toContain('text/html');
});