<?php

namespace nostriphant\BlossomTests;
use function \nostriphant\Blossom\data_directory;

require_once dirname(__DIR__) . '/bootstrap.php';

function files_directory() {
    return \nostriphant\Blossom\data_directory() . '/' . ($_ENV['FILES_DIRECTORY'] ?? 'files');
}
function make_files_directory() {
    return \nostriphant\Blossom\make_data_directory() && (is_dir(files_directory()) || mkdir(files_directory()));
}

function destroy_directories($path) {
    foreach (glob($path . '/*') as $node) {
        if (in_array(basename($node), ['.', '..'])) {
            continue;
        } elseif (is_file($node)) {
            unlink($node);
        } elseif (is_dir($node)) {
            destroy_directories($node);
            rmdir($node);
        }
    }
}