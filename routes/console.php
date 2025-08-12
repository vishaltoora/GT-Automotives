<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('gt:seed', function () {
    $this->info('Seeding GT Automotives database...');
    $this->call('db:seed');
    $this->info('Database seeded successfully!');
})->purpose('Seed the GT Automotives database with sample data');

Artisan::command('gt:migrate', function () {
    $this->info('Running GT Automotives migrations...');
    $this->call('migrate');
    $this->info('Migrations completed successfully!');
})->purpose('Run all GT Automotives database migrations'); 