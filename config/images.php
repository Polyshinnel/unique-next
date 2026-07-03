<?php

return [
    'webp' => [
        'quality' => env('IMAGE_WEBP_QUALITY', 82),
        'queue' => env('IMAGE_CONVERSION_QUEUE', 'images'),
        'delete_original_after_conversion' => env('IMAGE_DELETE_ORIGINAL_AFTER_CONVERSION', true),
        'convert_extensions' => ['jpg', 'jpeg', 'png'],
    ],
];
