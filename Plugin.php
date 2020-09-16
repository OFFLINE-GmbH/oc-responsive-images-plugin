<?php namespace OFFLINE\ResponsiveImages;

use Backend\FormWidgets\FileUpload;
use Cms\Classes\Theme;
use Illuminate\Support\Facades\Event;
use October\Rain\Exception\ApplicationException;
use OFFLINE\ResponsiveImages\Classes\Focuspoint\File as FocusFile;
use OFFLINE\ResponsiveImages\Classes\SVG\SVGInliner;
use OFFLINE\ResponsiveImages\Console\GenerateResizedImages;
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

        Event::listen('backend.page.beforeDisplay', function ($controller, $action, $params) {
            $controller->addJs('/plugins/offline/responsiveimages/widgets/fileupload/assets/js/focuspoint-tool.js');
        });

        File::extend(function (File $file) {
            $file->addDynamicMethod('focus', function ($width, $height, $options = []) use ($file) {
                return FocusFile::fromFileModel($file)->focus($width, $height, $options);
            });
        });

        FileUpload::extend(function (FileUpload $widget) {
            $isEnabled = (bool)($widget->config->focuspoint ?? false);
            if ($isEnabled !== true) {
                return;
            }
            $widget->addViewPath('plugins/offline/responsiveimages/widgets/fileupload/partials');
            $widget->addDynamicMethod('onSaveAttachmentConfigFocuspoint', function () use ($widget) {
                $original = $widget->onSaveAttachmentConfig();

                if (is_array($original) === false || array_key_exists('displayName', $original) === false) {
                    return $original;
                }

                try {
                    list($model, $attribute) = $widget->resolveModelAttribute($widget->valueFrom);
                    $fileModel = $model->makeRelation($attribute);

                    if (($fileId = post('file_id')) && ($file = $fileModel::find($fileId))) {
                        $file->offline_responsiveimages_focus_x_axis = post('offline_responsiveimages_focus_x_axis');
                        $file->offline_responsiveimages_focus_y_axis = post('offline_responsiveimages_focus_y_axis');
                        $file->title = post('title');
                        $file->description = post('description');
                        $file->save();

                        return $original;
                    }

                    throw new ApplicationException('Unable to find file, it may no longer exist');
                } catch (\Throwable $ex) {
                    return json_encode(['error' => $ex->getMessage()]);
                }
            });
        });
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
