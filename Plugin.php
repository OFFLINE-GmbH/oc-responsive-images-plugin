<?php namespace OFFLINE\ResponsiveImages;

use System\Classes\PluginBase;

/**
 * ResponsiveImages Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        $this->app['Illuminate\Contracts\Http\Kernel']
            ->pushMiddleware('OFFLINE\ResponsiveImages\Classes\ResponsiveImagesMiddleware');
    }

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'ResponsiveImages',
            'description' => 'Adds srcset and sizes attributes to your images',
            'author'      => 'OFFLINE',
            'icon'        => 'icon-file-image-o',
        ];
    }

}
