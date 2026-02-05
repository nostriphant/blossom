<?php

namespace {

    use nostriphant\BlossomTests\FeatureCase;
    
    
        
    /*
      |--------------------------------------------------------------------------
      | Test Case
      |--------------------------------------------------------------------------
      |
      | The closure you provide to your test functions is always bound to a specific PHPUnit test
      | case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
      | need to change it using the "uses()" function to bind a different classes or traits.
      |
     */

    pest()->extend(FeatureCase::class)
        ->group('feature')
        ->in('Feature')
        ->beforeAll(function() {
            expect(\nostriphant\BlossomTests\make_files_directory())->toBeTrue();
            expect(\nostriphant\BlossomTests\files_directory())->toBeDirectory();

            FeatureCase::relay_process();
        })
        ->afterAll(function() {
            FeatureCase::end_relay_process();

            \nostriphant\RelayTests\destroy_files_directory();
        });

}