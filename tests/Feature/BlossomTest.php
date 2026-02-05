<?php

namespace nostriphant\BlossomTests\Feature;

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