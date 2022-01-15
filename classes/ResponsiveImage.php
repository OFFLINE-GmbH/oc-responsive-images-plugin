<?php

namespace OFFLINE\ResponsiveImages\Classes;

use Cache;
use Config;
use File as FileHelper;
use Log;
use OFFLINE\ResponsiveImages\Classes\Exceptions\FileNotFoundException;
use OFFLINE\ResponsiveImages\Classes\Exceptions\RemotePathException;
use OFFLINE\ResponsiveImages\Classes\Exceptions\UnallowedFileTypeException;
use OFFLINE\ResponsiveImages\Models\Settings;
use System\Classes\MediaLibrary;
use URL;
use WebPConvert\WebPConvert;

/**
 * Creates the various copies of an image.
 *
 * @package OFFLINE\ResponsiveImages\Classes
 */
class ResponsiveImage
{
    /**
     * Absolute path to the image.
     *
     * @var string
     */
    protected $path;
    /**
     * @var string
     */
    protected $extension;
    /**
     * Where to save the image.
     *
     * @var string
     */
    protected $dir;
    /**
     * @var MediaLibrary
     */
    protected $mediaLibrary;
    /**
     * Filename without the path.
     *
     * @var string
     */
    protected $filename;
    /**
     * Where the various copies of the image saved.
     *
     * @var SourceSet
     */
    protected $sourceSet;
    /**
     * ImageResizer instance.
     *
     * @var ImageResizer
     */
    protected $resizer;
    /**
     * What copies of the image need to be created.
     *
     * These values are overridden by the plugin's
     * settings!
     *
     * @var array
     */
    protected $dimensions = '400,768,1024';

    /**
     * Only process these images.
     *
     * These values are overridden by the plugin's
     * settings!
     *
     * @var array
     */
    protected $allowedExtensions = 'jpg,jpeg,png,gif';

    /**
     * Focus-Coordinates
     *
     * @var array
     */
    protected $focus = [];

    /**
     * Create WebP images.
     *
     * @var boolean
     */
    protected $webPEnabled = false;

    /**
     * Create all the needed copies of the image.
     *
     * @param      $imagePath
     */
    public function __construct($imagePath)
    {
        $imagePath = urldecode($imagePath);
        $this->path = $this->normalizeImagePath($imagePath);

        if ( ! FileHelper::isLocalPath($this->path)) {
            throw new RemotePathException(sprintf('The specified path is not local: %s', $imagePath));
        }

        if ( ! file_exists($this->path)) {
            throw new FileNotFoundException(sprintf('The specified file does not exist: %s', $imagePath));
        }

        $this->loadSettings();
        $this->parseImagePath();

        $this->focus = [];

        $width = $this->getWidth();

        $this->sourceSet = new SourceSet($this->path, $width);

        $this->dimensions[] = $width;
        $this->createCopies();
    }

    /**
     * Returns and caches the image's original width.
     *
     * @return int
     */
    protected function getWidth()
    {
        $cacheKey = 'responsiveimages.widths.' . $this->getPathHash();

        return Cache::rememberForever($cacheKey, function () {
            // Use fastest method
            try {
                $size = getimagesize($this->path);
                if (isset($size[0]) && $size[0]) {
                    return $size[0];
                }
            } catch (\Exception $e) {
                if (Settings::get('log_unprocessable', false)) {
                    Log::warning(sprintf('Failed to run getimagesize for image "%s"', $this->path));
                }
            }

            // Fallback to heavy object
            return (new ImageResizer($this->path))->getWidth();
        });
    }

    /**
     * Returns an associative array of all available
     * image sizes and their storage locations.
     *
     * @return SourceSet
     */
    public function getSourceSet()
    {
        return $this->sourceSet;
    }

    /**
     * Creates the non existent copies of the image.
     *
     * @return bool
     */
    protected function createCopies()
    {
        $unavailableSizes = $this->getUnavailableSizes();

        // Only create ImageResizer if there are copies to be made.
        if (count($unavailableSizes) < 1) {
            return false;
        }

        foreach ($unavailableSizes as $size) {
            $this->createCopy($size);
        }
    }

    /**
     * Create a copy of the image for $size.
     *
     * @param $size
     */
    protected function createCopy($size)
    {
        try {
            // Only scale the image down
            if ($this->getWidth() < $size) {
                $this->sourceSet->remove($size);

                return;
            }

            // Load the image into a new resizer since the previous one was destroyed during save.
            $this->resizer = new ImageResizer($this->path);

            $saveTo = $this->getStoragePath($size);
            $this->resizer->resize($size, null)->save($saveTo);

            // Create webp images if the feature is enabled.
            if ($this->webPEnabled) {
                WebPConvert::convert($saveTo, $saveTo . '.webp', Settings::DEFAULT_WEBP_CONVERT_OPTIONS);
            }
        } catch (\Throwable $e) {
            // Cannot resize image to this size. Remove it from the srcset.
            $this->sourceSet->remove($size);

            if (Settings::get('log_unprocessable', false)) {
                Log::warning(sprintf('Failed to create size "%s" for image "%s"', $size, $this->path));
            }
        }
    }

    /**
     * Returns the absolute path for a image copy.
     *
     * @param $size
     *
     * @return string
     */
    protected function getStoragePath($size)
    {
        $path = temp_path('public/' . $this->getPartitionDirectory());
        if ( ! FileHelper::isDirectory($path)) {
            FileHelper::makeDirectory($path, 0777, true, true);
        }

        $storagePath = $path . $this->getStorageFilename($size);

        $this->sourceSet->push($size, $storagePath);

        return $storagePath;
    }

    /**
     * Returns the partition directory based on the image's path.
     *
     * @return string
     */
    protected function getPartitionDirectory()
    {
        $pieces = array_slice(str_split($this->getPathHash(), 3), 0, 3);

        return implode('/', $pieces) . '/';
    }

    /**
     * Returns the copy's filename.
     *
     * @param $size
     *
     * @return string
     */
    protected function getStorageFilename($size)
    {
        return $this->filename . '__' . $size . '.' . $this->extension;
    }

    /**
     * Returns an array of all non-existent image copies.
     *
     * @return array
     */
    protected function getUnavailableSizes()
    {
        $unavailableSizes = [];

        foreach ($this->dimensions as $size) {
            if ( ! file_exists($this->getStoragePath($size))) {
                $unavailableSizes[] = $size;
            }
        }

        return $unavailableSizes;
    }

    /**
     * Remove the local host name and add the base path.
     *
     * @param $imagePath
     *
     * @return mixed
     */
    protected function normalizeImagePath($imagePath)
    {
        $base = $this->getBase();

        $imagePath = trim(str_replace($base, '', $imagePath), '/');

        return base_path($imagePath);
    }

    /**
     * Returns the host base without subdirectories.
     */
    protected function getBase()
    {
        $protocol = \Request::server('HTTPS') ? 'https://' : 'http://';

        return $protocol . \Request::server('HTTP_HOST');
    }

    /**
     * Overwrites the defaults with user specified
     * config values.
     */
    private function loadSettings()
    {
        $this->dimensions = Settings::getCommaSeparated('dimensions', $this->dimensions);
        $this->allowedExtensions = Settings::getCommaSeparated('allowed_extensions', $this->allowedExtensions);
        $this->webPEnabled = (bool)Settings::get('webp_enabled', false);
    }

    /**
     * Extracts the filename and extension
     * out of the image path.
     *
     * @throws UnallowedFileTypeException
     */
    protected function parseImagePath()
    {
        $basename = basename($this->path);

        $this->filename = pathinfo($basename, PATHINFO_FILENAME);
        $this->extension = pathinfo($basename, PATHINFO_EXTENSION);

        if ( ! in_array($this->extension, $this->allowedExtensions)) {
            throw new UnallowedFileTypeException(
                sprintf('The specified file type "%s" is not allowed.', $this->extension)
            );
        }
    }

    /**
     * Returns the hashed file path.
     *
     * @return string
     */
    protected function getPathHash()
    {
        return md5($this->path);
    }
}
