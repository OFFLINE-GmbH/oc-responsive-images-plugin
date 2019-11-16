<?php
/**
 * Settings for OFFLINE.ResponsiveImages
 */
return [
    // What dimensions (width) to create of each image
    'dimensions'        => [
        400,
        768,
        1024,
    ],
    // What file extensions to process
    'allowedExtensions' => [
        'jpg',
        'jpeg',
        'png',
        'gif',
    ],
    // Use alternative attribute for image source, fallback remains "src"
    // set to false to disable
    'alternative-src'   => 'data-original',
];