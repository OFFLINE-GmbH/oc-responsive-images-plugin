<?php

namespace OFFLINE\ResponsiveImages\Classes\Focuspoint;

use October\Rain\Database\Attach\File as FileBase;

class File extends FileBase
{
    public $originalFile;

    public function focus($width, $height, $options = [])
    {
        return $this->getThumb($width, $height, $options);
    }

    public function getThumbFilename($width, $height, $options)
    {
        list($width, $height) = $this->normalizeSizes($width, $height);

        return 'offline-focus_' .
            $this->id . '_' .
            $width . '_' .
            $height . '_' .
            $this->offline_responsiveimages_focus_x_axis . '_' .
            $this->offline_responsiveimages_focus_y_axis . '_' .
            $options['offset'][0] . '_' .
            $options['offset'][1] . '_' .
            $options['mode'] . '.' .
            $options['extension'];
    }

    /**
     * Define the public address for the storage path.
     */
    public function getPublicPath()
    {
        return $this->originalFile->getPublicPath();
    }

    protected function calcSize($target, $originalSame, $originalOther)
    {
        return round($target / $originalSame * $originalOther);
    }

    /**
     * @param $width
     * @param $height
     *
     * @return array
     */
    protected function normalizeSizes($width, $height): array
    {
        if ($height === 0 && $width === 0) {
            $width  = $this->getWidthAttribute();
            $height = $this->getHeightAttribute();
        }
        if ($height === 0) {
            $height = $this->calcSize($width, $this->getWidthAttribute(), $this->getHeightAttribute());
        }
        if ($width === 0) {
            $width = $this->calcSize($height, $this->getHeightAttribute(), $this->getWidthAttribute());
        }

        return [$width, $height];
    }

    public static function fromFileModel(FileBase $file): File
    {
        $focusFile               = new self($file->attributesToArray());
        $focusFile->originalFile = $file;
        $focusFile->disk_name    = $file->disk_name;

        return $focusFile;
    }

    public function getOfflineResponsiveimagesFocusXAxisAttribute()
    {
        return $this->originalFile->offline_responsiveimages_focus_x_axis;
    }

    public function getOfflineResponsiveimagesFocusYAxisAttribute()
    {
        return $this->originalFile->offline_responsiveimages_focus_y_axis;
    }

}