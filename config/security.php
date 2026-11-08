<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Login Lockout
    |--------------------------------------------------------------------------
    |
    | Number of consecutive failed login attempts allowed before an account
    | is temporarily locked, and how long that lockout lasts. Ref: SR-03.
    |
    */

    'login' => [
        'max_attempts' => env('LOGIN_MAX_ATTEMPTS', 5),
        'lockout_minutes' => env('LOGIN_LOCKOUT_MINUTES', 1),
    ],

];
