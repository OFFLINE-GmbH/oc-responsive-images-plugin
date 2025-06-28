<?php

namespace OFFLINE\ResponsiveImages\Classes\Focuspoint;


use OFFLINE\ResponsiveImages\Classes\Focuspoint\File as FocusFile;
use System\Models\File;

class FocuspointExtension
{

    public static function boot()
    {
        // Add the focus method to the File model.
        File::extend(function (File $file) {
            $file->addDynamicMethod('focus', function ($width, $height, $options = []) use ($file) {
                return FocusFile::fromFileModel($file)->focus($width, $height, $options);
            });
        });
    }
}
