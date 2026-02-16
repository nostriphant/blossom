<?php


beforeAll(function(): void {
    is_dir(self::LOG_DIRECTORY) || mkdir(self::LOG_DIRECTORY);

    $descriptorspec = [
        0 => ["pipe", "r"],  
        1 => ["file", self::LOG_OUTPUT, "w"], 
        2 => ["file", self::LOG_ERRORS, "w"]
    ];
    self::$process = proc_open([PHP_BINARY, '-S', self::SOCKET, './tests/blossom.php'], $descriptorspec, $pipes, ROOT_DIR, [
        'BLOSSOM_ALLOWED_PUBKEYS' => '15b7c080c36d1823acc5b27b155edbf35558ef15665a6e003144700fc8efdb4f'
    ]);

    fclose($pipes[0]);

    while (str_contains(file_get_contents(self::LOG_ERRORS), 'Development Server (' . self::RELAY_URL . ') started') === false){ }
});
    
foreach (glob(__DIR__ . '/BUD*.php') as $test_file) {
    describe(basename($test_file, '.php'), function() use ($test_file) {
        require $test_file;
    });
    
}    


 afterAll(function(): void {
    proc_terminate(self::$process);
    sleep(1);

    proc_close(self::$process);
    unset(self::$process);

    unlink(self::LOG_ERRORS);
    unlink(self::LOG_OUTPUT);
    \nostriphant\RelayTests\destroy_files_directory();
});