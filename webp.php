<?php
$includes = [
    __DIR__ . '/../../../vendor/autoload.php',
    __DIR__ . '/vendor/autoload.php',
];

// Include the Composer autoloader.
foreach ($includes as $include) {
    if (file_exists($include)) {
        require $include;
    }
}

use WebPConvert\WebPConvert;

/**
 * Include Composer's autoloader, then convert and serve the WebP image.
 * @return void
 */
function main()
{
    $source = $_GET['path'];

    // Prefix the input path with a slash if it is not there already.
    if (strpos($source, '/') !== 0) {
        $source = '/' . $source;
    }

    $baseDir     = env('RESPONSIVE_IMAGES_BASE_DIR', __DIR__ . '/../../..');
    $source      = realpath($baseDir . $source);
    $destination = $source . '.webp';

    $path = validatePath($source);
    if ($path === '') {
        redirectNotFound();
        die();
    }

    WebPConvert::serveConverted($source, $destination, [
        // Serve the original if the client does not support WebP.
        'serve-original'       => strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') === false,

        'fail'                 => 'original',
        'fail-when-fail-fails' => '404',
        'serve-image'          => [
            'headers'              => [
                'cache-control'  => true,
                'content-length' => true,
                'content-type'   => true,
                'expires'        => false,
                'last-modified'  => true,
                'vary-accept'    => false,
            ],
            'cache-control-header' => 'public, max-age=31536000',
        ],
        'convert'              => [
	    'quality' => 95,
        ],
    ]);
}

main();

/**
 * Make sure no one is tampering with the path's GET parameter.
 *
 * @param string $path
 *
 * @return string
 */
function validatePath(string $path)
{
    if ( ! file_exists($path)) {
        return '';
    }

    $path = realpath($path);
    if ( ! realpath($path)) {
        return '';
    }

    if (strpos($path, '..') !== false) {
        return '';
    }

    if (strpos($path, './') !== false || strpos($path, '//') !== false) {
        return '';
    }

    // Get the project's base path based on the current directory.
    $basePath = str_replace('plugins/offline/responsiveimages', '', realpath(__DIR__));

    // Make sure the included path starts with the project's base path.
    if (strpos($path, $basePath) !== 0) {
        return '';
    }

    return $path;
}

/**
 * Redirect to October's /404 URL to trigger a not found response.
 * @return void
 */
function redirectNotFound()
{
    $location = sprintf('%s://%s/404', getProtocol(), $_SERVER['SERVER_NAME']);
    header('Location: ' . $location);
    die;
}

/**
 * Determine the request protocol of the current request.
 *
 * @return string
 */
function getProtocol(): string
{
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        return 'https';
    }
    if ( ! empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        return 'https';
    }
    if ( ! empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') {
        return 'https';
    }

    return 'http';
}
