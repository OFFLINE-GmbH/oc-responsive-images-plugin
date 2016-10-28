<?php return [
    'plugin'            => [
        'name'                       => 'ResponsiveImages',
        'description'                => 'Adds srcset and sizes attributes to your <img> tags',
        'author'                     => 'OFFLINE LLC',
        'manage_settings'            => 'Settings for responsive images',
        'manage_settings_permission' => 'Can access ResponsiveImages settings',
    ],

    'settings' => [

        'sections' => [
            'processing' => 'Processing',
            'processing_comment' => 'Configure the processing of your images',

            'html' => 'HTML',
            'html_comment' => 'HTML code settings',
        ],

        'dimensions' => 'Generated image sizes',
        'dimensions_comment' => 'Comma separated list of image widths to generate (in px)',

        'allowed_extensions' => 'Processed file extensions',
        'allowed_extensions_comment' => 'Comma separated list of file extensions that should be processed',

        'log_unprocessable' => 'Log unprocessable images',
        'log_unprocessable_comment' => 'Creates a log entry for every image that could not be processed',

        'alternative_src' => 'src-attribute',
        'alternative_src_comment' => 'Use this attribute as image source instead of "src". Useful for lazy-loading  plugins.',

        'alternative_src_set' => 'srcset-attribute',
        'alternative_src_set_comment' => 'Add the generated image sets to this attribute instead of "srcset". Useful for lazy-loading  plugins.',

        'add_class' => 'class attribute',
        'add_class_comment' => 'Add this class to every processed image. Useful if you use a css framework like Bootstrap.',
    ]
];