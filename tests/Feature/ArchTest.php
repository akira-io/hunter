<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Factories\Factory;

arch()->preset()->php();
arch()->preset()->laravel()
    ->ignoring(
        [
            'App\Http\Controllers\Auth\GithubAuthController',
            'App\Http\Controllers\Api\MessageController',
            'App\Http\Controllers\Auth\GoogleAuthController',
            'App\Http\Requests',
        ]);
arch()->preset()->security();

arch('controllers')
    ->expect('App\Http\Controllers')
    ->not->toBeUsed();

arch('avoid mutation')
    ->expect('App')
    ->classes()
    ->toBeReadonly()
    ->ignoring([
        'App\Exceptions',
        'App\Jobs',
        'App\Models',
        'App\Providers',
        'App\Services',
        'App\Http\Controllers\Auth\GithubAuthController',
        'App\Http\Middleware\HandleInertiaRequests',
        'App\Http\Requests',
        'App\Http\Resources',
        'App\Foundation\Inspiring',
        'App\Policies',
        'App\Events',
        'App\Console\Commands',

    ]);

arch('avoid inheritance')
    ->expect('App')
    ->classes()
    ->toExtendNothing()
    ->ignoring([
        'App\Models',
        'App\Exceptions',
        'App\Jobs',
        'App\Providers',
        'App\Services',
        'App\Http\Middleware\HandleInertiaRequests',
        'App\Http\Requests',
        'App\Http\Resources',
        'App\Foundation\Inspiring',
        'App\Console\Commands',
    ]);

arch('annotations')
    ->expect('App')
//    ->toHavePropertiesDocumented()
    ->toHaveMethodsDocumented();

arch('avoid open for extension')
    ->expect('App')
    ->classes()
    ->toBeFinal();

arch('avoid abstraction')
    ->expect('App')
    ->not->toBeAbstract()
    ->ignoring([
        'App/Http/Controllers/Controller',
    ]);

arch('factories')
    ->expect('Database\Factories')
    ->toExtend(Factory::class)
    ->toHaveMethod('definition')
    ->toOnlyBeUsedIn([
        'App\Models',
    ]);

arch('models')
    ->expect('App\Models')
    ->toHaveMethod('casts')
    ->toOnlyBeUsedIn([
        'App\Http',
        'App\Jobs',
        'App\Models',
        'App\Providers',
        'App\Actions',
        'App\Services',
        'Database\Factories',
        'Database\Seeders',
        'App\Policies',
        'App\Events',
        'App\Console\Commands',
    ]);

arch('actions')
    ->expect('App\Actions')
    ->toHaveMethod('handle');
