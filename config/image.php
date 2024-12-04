<?php
return [
    'optimizer' => [
        'max_size' => env('IMAGE_MAX_SIZE', 1536),
        'quality' => env('IMAGE_QUALITY', 85),
        'disk' => env('IMAGE_DISK', 'local'),
        'supported_formats' => ['webp', 'jpg', 'jpeg', 'heic'],
        'temp_path' => storage_path('app/temp/images'),
        'paths' => [
            'uploads' => 'uploads',
            'optimized' => 'optimized',
        ],
        'upload_limits' => [
            'max_file_size' => env('IMAGE_UPLOAD_MAX_SIZE', 10000), // в килобайтах
        ]
    ]
];
