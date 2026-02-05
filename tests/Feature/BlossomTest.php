<?php

namespace nostriphant\BlossomTests\Feature;

beforeAll(function() {
    expect(\nostriphant\BlossomTests\make_files_directory())->toBeTrue();
    expect(\nostriphant\BlossomTests\files_directory())->toBeDirectory();

    \nostriphant\BlossomTests\FeatureCase::relay_process();
});

it('supports BUD-01 (GET /<sha-256>)', function () {
    $hash = $this->writeFile('Hello World!');
    $body = $this->expectRelayResponse('/' . $hash, 200, 'text/plain');
    expect($body)->toBe('Hello World!');
});

it('supports BUD-01 (HEAD /<sha-256>)', function () {
    $hash = $this->writeFile('Hello World!');
    $body = $this->expectRelayResponse('/' . $hash, 200, 'text/plain', 'HEAD');
    expect($body)->toBeEmpty();
});

it('responds with 404 when file missing', function () {
    $body = $this->expectRelayResponse('/not-existing', 404, 'text/html');
});

afterAll(function() {
    \nostriphant\BlossomTests\FeatureCase::end_relay_process();

    \nostriphant\RelayTests\destroy_files_directory();
});