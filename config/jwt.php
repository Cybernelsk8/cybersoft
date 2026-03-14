<?php

return [
    'secret' => env('TOKEN_SECRET', 'default_secret'),
    'expired_token' => env('TOKEN_EXPIRED_HOURS', 1),
    'receivers' => [
        'web' => 'web',
        'mobile' => 'mobile',
    ],
];