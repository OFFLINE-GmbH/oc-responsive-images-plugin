<?php return [
    'plugin'            => [
        'name'                       => 'ResponsiveImages',
        'description'                => 'Adds srcset and sizes attributes to your <img> tags',
        'author'                     => 'OFFLINE LLC',
        'manage_settings'            => 'Settings for responsive images',
        'manage_settings_permission' => 'Can access ResponsiveImages settings',
    ],

    'settings' => [

        'tabs' => [
            'responsive_image' => 'Responsive Image',
            'focuspoint' => 'Focus-Point'
        ],

        'sections' => [
            'processing' => 'Processing',
            'processing_comment' => 'Configure the processing of your images',

            'webp' => 'WebP',
            'webp_comment' => 'Configure the WebP image conversion process',

            'html' => 'HTML',
            'html_comment' => 'HTML code settings',

            'focuspoint' => 'Focuspoint',
            'focuspoint_comment' => 'Focuspoint related settings',
        ],

        'dimensions' => 'Generated image sizes',
        'dimensions_comment' => 'Comma separated list of image widths to generate (in px)',

        'allowed_extensions' => 'Processed file extensions',
        'allowed_extensions_comment' => 'Comma separated list of file extensions that should be processed',

        'use_real_path' => 'Use real path',
        'use_real_path_comment' => 'Disabling this may be required if you use symlinks in your installation',

        'webp_enabled' => 'Enable WebP support',
        'webp_enabled_comment' => 'Images are automatically served in the WebP format to supported browsers. Please read the README on GitHub before enabling this option!',

        'webp_auto_convert' => 'Convert images automatically (not recommended)',
        'webp_auto_convert_comment' => 'Adds a rule to your .htaccess file that triggers the WebP conversion when an image is requested for the first time. It is recommended to use the responsive-images:convert artisan command in a cronjob (see README).',

        'webp_partial' => [
            'title' => 'WebP support',
            'text' => 'This feature only supports Apache + .htaccess out-of-the-box. All other servers require additional configuration, see',
        ],

        'latest_conversion' => 'Latest image conversion',
        'latest_conversion_comment' => 'Timestamp of the latest image conversion by the console command',

        'log_unprocessable' => 'Log unprocessable images',
        'log_unprocessable_comment' => 'Creates a log entry for every image that could not be processed',

        'alternative_src' => 'src attributes (comma-separated)',
        'alternative_src_comment' => 'Use this attribute as image source. Useful for lazy-loading plugins.',

        'alternative_src_set' => 'srcset attributes (comma-separated)',
        'alternative_src_set_comment' => 'Add the generated image sets to this attribute. Each "src" needs a "srcset" partner here (same number of attributes)',

        'add_class' => 'class attribute',
        'add_class_comment' => 'Add this class to every processed image. Useful if you use a css framework like Bootstrap.',

        'focuspoint_class' => 'class attribute',
        'focuspoint_class_comment' => 'Custom class for focuspoint image (standard: .focuspoint-image)',

        'focuspoint_container_class' => 'container class Attribute',
        'focuspoint_container_class_comment' => 'Class for focuspoint container (empty = don\' add container, Standard: disabled)',

        'focuspoint_data_x' => 'data-attribute for x-axis',
        'focuspoint_data_x_comment' => 'The X-coordinate of the focus point will be injected into this data attribute (eg. data-focus-x="40", Standard: disabled)',

        'focuspoint_data_y' => 'data-attribute for y-axis',
        'focuspoint_data_y_comment' => 'The Y-coordinate of the focus point will be injected into this data attribute (eg. data-focus-y="40", Standard: disabled)',

        'allow_inline_object' => 'Inline-CSS for object-fit and object-position',
        'allow_inline_object_comment' => 'Inject the object-* CSS rules directly as style attribute into the HTML source',

        'allow_inline_sizing' => 'Inline-CSS for image width and height',
        'allow_inline_sizing_comment' => 'Inject the width and height CSS rules directly as style attribute into the HTML source',
    ],

    'fields' => [
        'focus_x_axis' => 'Focus point X-axis',
        'focus_y_axis' => 'Focus point Y-axis',
    ],

    'reportwidgets' => [
        'clearcache' => [
            'label' => 'Responsive Images: Clear cache',
            'description' => 'Click the button below to clear all cached images.',
            'button' => 'Clear image cache',
            'confirm' => 'Are you sure you want to clear the image cache?',
            'success' => 'Image cache successfully cleared',
        ],
    ]
];
