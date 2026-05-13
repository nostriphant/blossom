<?php

namespace nostriphant\Blossom;

require_once __DIR__ . '/vendor/autoload.php';

define('ROOT_DIR', __DIR__);
function data_directory() {
    return __DIR__ . '/data';
}
function make_data_directory() {
    return is_dir(data_directory()) || mkdir(data_directory());
}
function destroy_data_directory() {
    return true;
}
function destroy_directories(string $path) {
    foreach (glob($path.'/*') as $subpath) {
        if ($subpath === '.' || $subpath === '..') {
            continue;
        } elseif (is_file($subpath)) {
            unlink($subpath);
        } else {
            destroy_directorys($subpath);
        }
    }
    return rmdir($path);
}