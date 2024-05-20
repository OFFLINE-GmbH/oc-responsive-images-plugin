<?php

namespace OFFLINE\ResponsiveImages\Classes\Focuspoint;


use Illuminate\Support\Facades\Event;
use OFFLINE\ResponsiveImages\Classes\Focuspoint\File as FocusFile;
use System\Models\File;

class FocuspointExtension
{

    public static function boot()
    {
        // Register Backend Assets.
        Event::listen('backend.page.beforeDisplay', function ($controller, $action, $params) {
            $controller->addJs('/plugins/offline/responsiveimages/assets/js/focuspoint-tool.js');
        });

        // Add the X and Y Focus point fields to the file upload popup.
        Event::listen('system.extendConfigFile', function (string $path, $config = []) {
            if ($path === '/modules/system/models/file/fields.yaml') {
                $config['fields']['offline_responsiveimages_focus_x_axis'] = [
                    'label' => 'offline.responsiveimages::lang.fields.focus_x_axis',
                    'type' => 'number',
                    'span' => 'left',
                    'cssClass' => 'focuspoint-x-axis',
                    'readOnly' => true,
                ];
                $config['fields']['offline_responsiveimages_focus_y_axis'] = [
                    'label' => 'offline.responsiveimages::lang.fields.focus_y_axis',
                    'type' => 'number',
                    'span' => 'right',
                    'cssClass' => 'focuspoint-y-axis',
                    'readOnly' => true,
                ];
                return $config;
            }
        });

        // Add the focus method to the File model.
        File::extend(function (File $file) {
            $file->addDynamicMethod('focus', function ($width, $height, $options = []) use ($file) {
                return FocusFile::fromFileModel($file)->focus($width, $height, $options);
            });
        });
    }
}
