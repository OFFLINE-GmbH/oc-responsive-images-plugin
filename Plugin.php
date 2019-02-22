<?php namespace OFFLINE\ResponsiveImages;

use Cms\Classes\Theme;
use OFFLINE\ResponsiveImages\Classes\SVG\SVGInliner;
use OFFLINE\ResponsiveImages\Classes\ImagePreloader;
use OFFLINE\ResponsiveImages\Console\GenerateResizedImages;
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
            'name'        => 'offline.responsiveimages::lang.plugin.name',
            'description' => 'offline.responsiveimages::lang.plugin.description',
            'author'      => 'offline.responsiveimages::lang.plugin.author',
            'homepage'    => 'https://github.com/OFFLINE-GmbH/oc-responsive-images-plugin',
            'icon'        => 'icon-file-image-o'
        ];
    }

    /**
     * Registers any back-end permissions.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'offline.responsiveimages.manage_settings' => [
                    'tab'   => 'offline.responsiveimages::lang.plugin.name',
                'label' => 'offline.responsiveimages::lang.plugin.manage_settings_permission',
            ],
        ];
    }

    /**
     * Registers any back-end settings.
     *
     * @return array
     */
    public function registerSettings()
    {
        return [
            'config' => [
                'label'       => 'offline.responsiveimages::lang.plugin.name',
                'description' => 'offline.responsiveimages::lang.plugin.manage_settings',
                'category'    => 'system::lang.system.categories.cms',
                'icon'        => 'icon-file-image-o',
                'class'       => 'Offline\ResponsiveImages\Models\Settings',
                'order'       => 500,
                'keywords'    => 'responsive images',
                'permissions' => ['offline.responsiveimages.manage_settings']
            ],
        ];
    }

    public function register(){
        $this->registerConsoleCommand('responsiveimages:generate', GenerateResizedImages::class);
    }

    public function registerMarkupTags()
    {
        return [
            'functions' => [
                'svg' => function ($path, $vars = []) {
                    $themeDir = Theme::getActiveThemeCode();

                    return (new SVGInliner($themeDir))->inline($path, $vars);
                },
            ],
        ];
    }

}
