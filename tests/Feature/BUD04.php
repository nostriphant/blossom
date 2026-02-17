<?php

namespace nostriphant\BlossomTests\Feature;

use \nostriphant\BlossomTests\FeatureCase;

    
describe("OPTIONS /mirror", function() {
    it('For preflight (OPTIONS) requests, servers MUST also set, at minimum, the Access-Control-Allow-Headers: Authorization, * and Access-Control-Allow-Methods: PUT headers.', function () {
        $hash = \nostriphant\Blossom\writeFile(FILES_DIRECTORY, 'Hello World!');
        list($protocol, $status, $headers, $body) = FeatureCase::request('OPTIONS', '/upload');
        expect($status)->toBe('204');
        expect($headers['access-control-allow-origin'])->toBe('Authorization, *');
        expect(explode(', ', $headers['access-control-allow-methods']))->toContain('PUT');
        expect($body)->toBeEmpty();
        
        \nostriphant\Blossom\deleteFile(FILES_DIRECTORY, $hash);
    });

    it('The header Access-Control-Max-Age: 86400 MAY be set to cache the results of a preflight request for 24 hours.', function () {
        $hash = \nostriphant\Blossom\writeFile(FILES_DIRECTORY, 'Hello World!');
        list($protocol, $status, $headers, $body) = FeatureCase::request('OPTIONS', '/upload');
        expect($headers['access-control-max-age'])->toBe('86400');
        \nostriphant\Blossom\deleteFile(FILES_DIRECTORY, $hash);
    });
});

it('The /mirror endpoint MUST download the blob from the specified URL and verify that there is at least one x tag in the authorization event matching the sha256 hash of the download blob', function (string $contents, string $hash) {

    $blossom = FeatureCase::start_blossom('127.0.0.1:8088', FeatureCase::LOG_DIRECTORY . "/blossom-8088.log", FeatureCase::LOG_DIRECTORY . "/blossom-errors-8088.log");
    
    try {
        $resource = tmpfile();
        fwrite($resource, $contents);
        fseek($resource, 0);

        $hash_file = $blossom->files_directory . DIRECTORY_SEPARATOR . $hash;

        list($protocol, $status, $headers, $body) = FeatureCase::request('PUT', '/upload', upload_resource: $resource, authorization:['t' => 'upload', 'x' => $hash]);
        expect($status)->toBe('201');

        $expected_hash = hash('sha256', 'Hello World!!!');
        $blob_descriptor = json_decode($body);
        expect($blob_descriptor->url)->toBe(FeatureCase::RELAY_URL . '/' . $expected_hash);
        expect($blob_descriptor->sha256)->toBe($expected_hash);
        expect($blob_descriptor->size)->toBe(14);
        expect($blob_descriptor->type)->toBe('text/plain');
        expect($blob_descriptor->uploaded)->toBeInt();

        $mirror_content = '{"url": "'.$blob_descriptor->url.'"}';
        list($protocol, $status, $headers, $body) = FeatureCase::request('PUT', 'http://127.0.0.1:8088/mirror', upload_resource: $mirror_content, authorization:['t' => 'upload', 'x' => $hash]);
        expect($status)->toBe('201');
        
        $blob_descriptor = json_decode($body);
        expect($blob_descriptor->url)->toBe(FeatureCase::RELAY_URL . '/' . $expected_hash);
        expect($blob_descriptor->sha256)->toBe($expected_hash);
        expect($blob_descriptor->size)->toBe(14);
        expect($blob_descriptor->type)->toBe('text/plain');
        expect($blob_descriptor->uploaded)->toBeInt();


        expect($headers['content-location'])->toBe('/' . $expected_hash);
        expect((int)$headers['content-length'])->toBe(strlen($body));
        expect($headers['access-control-allow-origin'])->toBe('*');

        expect($hash_file)->toBeFile();
        expect($hash_file . '.owners')->toBeDirectory();
        expect($hash_file . '.owners' . DIRECTORY_SEPARATOR . '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f')->toBeFile();

        list($protocol, $status, $headers, $body) = FeatureCase::request('DELETE', 'http://127.0.0.1:8088/' . $blob_descriptor->sha256, authorization:['t' => 'delete', 'x' => $hash]);
        expect($status)->toBe('204');
        expect($headers['access-control-allow-origin'])->toBe('*');

        clearstatcache();
        expect($hash_file)->not()->toBeFile();
        expect($hash_file . '.owners' . DIRECTORY_SEPARATOR . '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f')->not()->toBeFile();
        expect(glob($hash_file . '.owners/*'))->toHaveCount(0);
        expect($hash_file . '.owners')->not()->toBeDirectory();
    } catch (\Exception $e) {
        $blossom(false);
        throw $e;
    }
    $blossom();
})->with([
    [$contents = 'Hello World!!!', hash('sha256', $contents)]
]);