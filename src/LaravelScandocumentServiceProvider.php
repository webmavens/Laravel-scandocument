<?php

namespace Webmavens\LaravelScandocument;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelScandocumentServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravelscandocument')
            ->hasConfigFile()
            ->hasMigration('create_laravelscandocument_table')
            ->hasRoute('web');
    }
}
