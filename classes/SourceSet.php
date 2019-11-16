<?php

namespace OFFLINE\ResponsiveImages\Classes;

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
     * WebP helper class.
     *
     * @var WebP
     */
    public $webP = '';

    /**
     * SourceSet constructor.
     *
     * @param      $originalPath
     * @param      $originalWidth
     * @param WebP $webP
     */
    public function __construct($originalPath, $originalWidth, WebP $webP)
    {
        $this->originalWidth = $originalWidth;
        $this->webP          = $webP;

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
        // Replace any Backslashes for Windows compatibility
        $relativePath = str_replace('\\', '/', $relativePath);

        $filename           = basename($relativePath);
        $relativeFolderPath = str_replace($filename, '', $relativePath);

        return $this->webP->prefix($relativeFolderPath . $filename);
    }

    /**
     * Generates the src attribute.
     *
     * @return string
     */
    public function getSrcAttribute()
    {
        return $this->rules[$this->originalWidth]['public_url'] ?? '';
    }

    /**
     * Generates the srcset attribute.
     *
     * @return string
     */
    public function getSrcSetAttribute(string $existing)
    {
        $attribute = [];

        if ($existing !== '') {
            return $this->wrapExisting($existing);
        }

        foreach ($this->rules as $size => $paths) {
            if (is_numeric($size)) {
                $sizePlaceholder = '%dw'; // Add a "w" suffix to numberic sizes.
            } else {
                $sizePlaceholder = '%s'; // Use a simple string placeholder for everything else.
            }
            $attribute[] = sprintf('%s ' . $sizePlaceholder, $paths['public_url'], $size);
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

    /**
     * Wrap the existing srcset with the WebP prefix.
     *
     * @param string $existing
     *
     * @return mixed
     */
    protected function wrapExisting(string $existing)
    {
        $parts = explode(', ', $existing);
        foreach ($parts as $key => $part) {
           $values = explode(' ', $part);
           $values[0] = $this->webP->prefix($values[0]);
           $parts[$key] = implode(' ', $values);
        }
        return implode(', ', $parts);
    }
}
