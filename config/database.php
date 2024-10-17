<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            // 'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'db'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_NAME', 'performance'),
            'username' => env('DB_USER', 'performance'),
            'password' => env('DB_PASSWORD', 'performance'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => false,
            'engine' => null,
            // 'options' => extension_loaded('pdo_mysql') ? array_filter([
            //     PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            // ]) : [],
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'lock_connection' => 'default',
        ],

        'default' => [
            'host' => env('REDIS_HOST', 'redis'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DATABASE', 0)
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'predis'),

        // 'options' => [
        //     'cluster' => env('REDIS_CLUSTER', false),
        //     'prefix' => env(
        //         'REDIS_PREFIX',
        //         Str::slug(env('APP_NAME', 'pe   rformance'), '_').'_database_'
        //     ),
        // ],

        'default' => [
            'url' => env('REDIS_URL', 'tcp://redis-0:6379'),
            'host' => env('REDIS_HOST', 'redis-0'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DATABASE', 0)
        ],

        'redis' => [
            'url' => env('REDIS_URL', 'tcp://redis-0:6379'),
            'cluster' => false,
            'default' => [
                'host' => env('REDIS_HOST', 'redis-0'),
                'password' => env('REDIS_PASSWORD', null),
                'port' => env('REDIS_PORT', '6379'),
                'database' => env('REDIS_DATABASE', 0)
            ]
        ],

        'predis' => [
            'url' => env('REDIS_URL', 'tcp://redis-0:6379'),
            'cluster' => false,
            'default' => [
                'host' => env('REDIS_HOST', 'redis-0'),
                'password' => env('REDIS_PASSWORD', null),
                'port' => env('REDIS_PORT', '6379'),
                'database' => env('REDIS_DATABASE', 0)
            ]
        ],

        'session' => [
            // 'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', 'redis-0'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_SESSION_DB', '0'),
            'prefix' => 's:'
        ],

        'cache' => [
            // 'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', 'redis-0'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '0')
        ],
    ],
];
