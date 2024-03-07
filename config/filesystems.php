<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been set up for each driver as an example of the required values.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */
    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => public_path(),
        ],

        'downloads' => [
            'driver' => 'local',
            'root' => storage_path('downloads'),
        ],

        'reports' => [
            'driver' => 'sftp',
            'host' => env('REPORTS_FTP_HOST', 'nlr.engageiq.com'),
            'username' => env('REPORTS_FTP_USERNAME', 'USERNAME'),
            'password' => env('REPORTS_FTP_PASSWORD', 'PASSWORD'),
            'port' => env('REPORTS_FTP_PORT', '21'),
            'passive' => false,
            'root' => env('REPORTS_FTP_DIR', '/var/www/html/tlr.engageiq.com/storage/downloads'),
        ],

        'main' => [
            'driver' => 'sftp',
            'host' => env('MAIN_FTP_HOST', 'nlr.engageiq.com'),
            'username' => env('MAIN_FTP_USERNAME', 'USERNAME'),
            'password' => env('MAIN_FTP_PASSWORD', 'PASSWORD'),
            'port' => env('MAIN_FTP_PORT', '21'),
            'passive' => false,
            'root' => env('MAIN_FTP_DIR', '/var/www/html/devleadreactor.engageiq.com/storage/downloads'),
        ],

        'main_slave' => [
            'driver' => 'sftp',
            'host' => env('MAIN_SLAVE_FTP_HOST', 'nlr.engageiq.com'),
            'username' => env('MAIN_SLAVE_FTP_USERNAME', 'USERNAME'),
            'password' => env('MAIN_SLAVE_FTP_PASSWORD', 'PASSWORD'),
            'port' => env('MAIN_SLAVE_FTP_PORT', '21'),
            'passive' => false,
            'root' => env('MAIN_SLAVE_FTP_DIR', '/var/www/html/devleadreactor.engageiq.com/storage/downloads'),
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
