<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Admin Account
    |--------------------------------------------------------------------------
    |
    | `php artisan db:seed` creates this super admin unless an admin with the
    | same email already exists. With no password set, it uses "password" in
    | the local environment and a random one (printed once) anywhere else.
    |
    */

    'seed' => [
        'name' => env('ADMIN_NAME', 'CAGAD Admin'),
        'email' => env('ADMIN_EMAIL', 'admin@cagad.org'),
        'password' => env('ADMIN_PASSWORD'),
    ],

];
