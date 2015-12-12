<?php

namespace OFFLINE\ResponsiveImages\Classes;

use Cms\Classes\MediaLibrary;
use File as FileHelper;
use URL;

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
     * @var array
     */
    protected $srcSet = [];

    /**
     * What copies of the image need to be created.
     *
     * @var array
     */
    protected $sizes = [
        '400',
        '768',
        '1024',
    ];

    /**
     * Only process these images.
     *
     * @var array
     */
    protected $allowedExtensions = [
        'jpg',
        'jpeg',
        'png',
        'gif',
    ];

    /**
     * Create all the needed copies of the image.
     *
     * @param $imagePath
     */
    public function __construct($imagePath)
    {
        $this->mediaLibrary = MediaLibrary::instance();
        $this->path         = $this->normalizeImagePath($imagePath);

        if ( ! FileHelper::isLocalPath($this->path)) {
            throw new \InvalidArgumentException('The specified path is not local.');
        }

        if ( ! file_exists($this->path)) {
            throw new \InvalidArgumentException('The specified file does not exist.');
        }

        $basename = basename($this->path);

        $this->filename  = pathinfo($basename, PATHINFO_FILENAME);
        $this->extension = pathinfo($basename, PATHINFO_EXTENSION);

        if ( ! in_array($this->extension, $this->allowedExtensions)) {
            throw new \InvalidArgumentException('The specified file type is not allowed.');
        }

        $this->createCopies();
    }

    /**
     * Returns an associative array of all available
     * image sizes and their storage locations.
     *
     * @return array
     */
    public function getSrcSet()
    {
        $srcSet = [];

        foreach ($this->srcSet as $size => $path) {
            $srcSet[$size] = $this->stripBasePath($path);
        }

        return $srcSet;
    }

    /**
     * Creates the non existent copies of the image.
     *
     * @return bool
     */
    protected function createCopies()
    {
        $unavailableSizes = $this->getUnavailableSizes();

        // Only create a ImageResizer object if copies
        // need to be generated.
        if (count($unavailableSizes) < 1) {
            return true;
        }

        $this->resizer = new ImageResizer($this->path);

        foreach ($unavailableSizes as $size) {
            $this->createCopy($size);
        }

        return true;
    }

    /**
     * Create a copy of the image for $size.
     *
     * @param $size
     */
    protected function createCopy($size)
    {
        // Only scale the image down
        if ($this->resizer->getWidth() < $size) {
            unset($this->srcSet[$size]);
            return;
        }

        try {
            $this->resizer
                ->resize($size, null)
                ->save($this->getStoragePath($size));
        } catch (\Exception $e) {
            // Cannot resize image to this size. Remove it from the srcset.
            unset($this->srcSet[$size]);
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

        $storagePath         = $path . $this->getStorageFilename($size);
        $this->srcSet[$size] = $storagePath;

        return $storagePath;
    }

    /**
     * Returns the partition directory based on the image's path.
     *
     * @return string
     */
    protected function getPartitionDirectory()
    {
        return implode('/', array_slice(str_split(md5($this->path), 3), 0, 3)) . '/';
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

        foreach ($this->sizes as $size) {
            if ( ! file_exists($this->getStoragePath($size))) {
                $unavailableSizes[] = $size;
            }
        }

        return $unavailableSizes;
    }

    /**
     * Removes the base path from $path.
     *
     * @param $path
     *
     * @return mixed
     */
    protected function stripBasePath($path)
    {
        return str_replace(base_path(), '', $path);
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
        return str_replace(URL::to('/'), '', base_path($imagePath));
    }
}