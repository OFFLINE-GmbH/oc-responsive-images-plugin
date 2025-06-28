<?php namespace OFFLINE\ResponsiveImages;

use Backend\FormWidgets\FileUpload;
use Cms\Classes\Theme;
use Illuminate\Support\Facades\Event;
use October\Rain\Exception\ApplicationException;
use OFFLINE\ResponsiveImages\Classes\Focuspoint\File as FocusFile;
use OFFLINE\ResponsiveImages\Classes\Focuspoint\FocuspointExtension;
use OFFLINE\ResponsiveImages\Classes\SVG\SVGInliner;
use OFFLINE\ResponsiveImages\Console\ConvertCommand;
use OFFLINE\ResponsiveImages\Console\GenerateResizedImages;
use OFFLINE\ResponsiveImages\Console\Clear;
use OFFLINE\ResponsiveImages\FormWidgets\FocusPointFileUpload;
use System\Classes\PluginBase;
use System\Models\File;
use System\Traits\AssetMaker;
use URL;

/**
 * ResponsiveImages Plugin Information File
 */
class Plugin extends PluginBase
{
    use AssetMaker;

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        $this->app['Illuminate\Contracts\Http\Kernel']
            ->pushMiddleware('OFFLINE\ResponsiveImages\Classes\ResponsiveImagesMiddleware');

        FocuspointExtension::boot();
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
            'icon'        => 'icon-file-image-o',
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
                'permissions' => ['offline.responsiveimages.manage_settings'],
            ],
        ];
    }

    public function register()
    {
        $this->registerConsoleCommand('responsiveimages:generate', GenerateResizedImages::class);
        $this->registerConsoleCommand('responsiveimages:convert', ConvertCommand::class);
        $this->registerConsoleCommand('responsiveimages:clear', Clear::class);
    }

    public function registerMarkupTags()
    {
        return [
            'functions' => [
                'svg' => [function ($path, $vars = []) {
                    $theme = Theme::getActiveTheme();
                    if (!$theme) {
                        return '';
                    }

                    $themeDir = $theme->getId();

                    // Try to fetch the file from the current theme.
                    $themePath = themes_path(implode('/', [$themeDir, $path]));
                    // If the file does not exist, check if there is a parent theme.
                    if (!file_exists($themePath) && $parentTheme = $theme->getParentTheme()) {
                        $themeDir = $parentTheme->getId();
                        $parentThemeDir = themes_path(implode('/', [$parentTheme->getId(), $path]));
                        if (file_exists($path)) {
                            $path = $parentThemeDir;
                        }
                    }

                    return (new SVGInliner($themeDir))->inline($path, $vars);
                }, false],
            ],
        ];
    }

    /**
     * Returns the extra report widgets.
     *
     * @return  array
     */
    public function registerReportWidgets()
    {
        return [
            'OFFLINE\ResponsiveImages\ReportWidgets\ClearCache' => [
                'label'   => 'offline.responsiveimages::lang.reportwidgets.clearcache.label',
                'context' => 'dashboard'
            ]
        ];
    }

    public function registerFormWidgets()
    {
        return [
            FocusPointFileUpload::class => 'focuspoint',
        ];
    }

}
