<?php

return [
    'stateful' => array_filter(array_map('trim', explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost')))),

    'guard' => ['sanctum'],

    'expiration' => null,

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
    ],
];
