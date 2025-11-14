<?php

return [
    'name' => env('APP_NAME', 'HRassess'),

    'env' => env('APP_ENV', 'production'),

    'debug' => (bool) env('APP_DEBUG', false),

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL'),

    'timezone' => 'UTC',

    'locale' => 'en',

    'fallback_locale' => 'en',

    'faker_locale' => 'en_US',

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    'providers' => [
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Routing\RoutingServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Events\EventServiceProvider::class,
        Laravel\Sanctum\SanctumServiceProvider::class,
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
    ],

    'aliases' => [
        'App' => Illuminate\Support\Facades\App::class,
        'Arr' => Illuminate\Support\Arr::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Route' => Illuminate\Support\Facades\Route::class,
    ],
];
