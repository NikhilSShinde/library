<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],
    'facebook'=>[
		 'client_id' => '276394649396754',
		'client_secret' => '4778d37480e8b5cfa3d285f690e75b9f',
		//'redirect' => 'http://localhost/laravel_pipl_lib/public/auth/facebook/callback',
		'redirect' => 'http://192.168.2.22:8079/GITLARAVELLIB/auth/facebook/callback',
	],
	'twitter'=>[
		 'client_id' => '2OaW0mi1rmkps2FKTk3ceAd7e',
		'client_secret' => 'EFd4HgQ39P4nIaGsFxwfuX6zNeo3ttMOQoPQAtXkZakubnosbx',
                 'redirect' => 'http://192.168.2.22:8079/GITLARAVELLIB/auth/twitter/callback',
//		'redirect' => 'http://localhost/laravel_pipl_lib/public/auth/twitter/callback',
	],
	'google'=>[
		 'client_id' => '298327175558-1k0nk3kt6tfb7vm1n3o4r3ki9htompf8.apps.googleusercontent.com',
		'client_secret' => 'mwQl6rmP2wjzDgHDIwkvKdth',
                'redirect' => 'http://localhost:8079/GITLARAVELLIB/auth/google/callback',
		//'redirect' => 'http://localhost/laravel_pipl_lib/public/auth/google/callback',
	],
	
	'instagram' => [
		'client_id' => 'a55e5bc735ca4db08aad7998ce3b0154',
		'client_secret' => '1bb217447a7e402aa6e0a2da46d8d9ad',
		'redirect' => 'http://localhost/laravel_pipl_lib/public/auth/instagram/callback',
	],
	

];
