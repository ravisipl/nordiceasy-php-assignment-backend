<?php

return [
    'defaults' => [
        'guard' => 'api',
        'passwords' => 'users',
    ],
    'guards' => [
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ],
    ],
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => \App\Models\User::class
        ]
    ],
    'jwt' => [
        'secret' => env('JWT_SECRET'),
        'ttl' => 240, // Expiration time in minutes
        'refresh_ttl' => 20160,
        'algo' => 'HS256',
        'required_claims' => [
            'iss',
            'iat',
            'exp',
            'nbf',
            'sub',
            'jti',
        ],
    ],
];


