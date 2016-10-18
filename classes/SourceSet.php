<?php

namespace OFFLINE\ResponsiveImages\Classes;

use Illuminate\Support\Facades\URL;

class SourceSet
{
    /**
     * The original width of the image.
     *
     * @var int
     */
    public $originalWidth;
    /**
     * The srcset rules.
     *
     * @var array
     */
    public $rules = [];
    /**
     * @var string
     */
    private $originalPath;

    /**
     * SourceSet constructor.
     *
     * @param $originalPath
     * @param $originalWidth
     */
    public function __construct($originalPath, $originalWidth)
    {
        $this->originalWidth = $originalWidth;
        $this->originalPath  = $originalPath;

        $this->push($originalWidth, $originalPath);
    }

    /**
     * Adds a new rule.
     *
     * @param $size
     * @param $storagePath
     */
    public function push($size, $storagePath)
    {
        $this->rules[$size] = ['storage_path' => $storagePath, 'public_url' => $this->getPublicUrl($storagePath)];
    }

    /**
     * Removes a rule.
     *
     * @param $size
     */
    public function remove($size)
    {
        unset($this->rules[$size]);
    }

    /**
     * Generates the public url for a path
     *
     * @param $path
     *
     * @return mixed
     */
    protected function getPublicUrl($path)
    {
        $relativePath = str_replace(base_path(), '', $path);
        $filename = basename($relativePath);
        $relativeFolderPath = str_replace($filename, '', $relativePath);

        return URL::to('/') . $relativeFolderPath . rawurlencode($filename) ;
    }

    /**
     * Generates the srcset attribute.
     *
     * @return string
     */
    public function getSrcSetAttribute()
    {
        $attribute = [];

        foreach ($this->rules as $size => $paths) {
            $attribute[] = sprintf('%s %sw', $paths['public_url'], $size);
        }

        return implode($attribute, ', ');
    }

    /**
     * Generates the sizes attribute.
     *
     * @param $width
     *
     * @return string
     */
    public function getSizesAttribute($width)
    {
        if ($width === '') {
            $width = $this->originalWidth;
        }

        return $width === '' ? '100vw' : sprintf('(max-width: %1$dpx) 100vw, %1$dpx', $width);
    }

}