<?php namespace OFFLINE\ResponsiveImages\Models;

use Model;
use October\Rain\Support\Facades\Flash;
use OFFLINE\ResponsiveImages\Classes\Htaccess\HtaccessManager;

class Settings extends Model
{
    /**
     * Default prefix for WebP images.
     * @var string
     */
    const DEFAULT_WEBP_PREFIX = 'plugins/offline/responsiveimages/webp.php?path=';
    const DEFAULT_WEBP_CONVERT_OPTIONS = [
        'converters' => [
            // Use only the native PHP image libraries since all other calls by the webp converter library
            // rely on the exec() function which might lead to dangerous situations.
            'vips',
            'imagick',
            'gmagick',
            'gd',
        ],
    ];

    public $implement = ['System.Behaviors.SettingsModel'];

    // A unique code
    public $settingsCode = 'offline_responsiveimages_settings';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';

    public function initSettingsData()
    {
        $this->dimensions = '400,768,1024';
        $this->allowed_extensions = 'jpg,jpeg,png,gif';
        $this->alternative_src = 'src, data-src';
        $this->alternative_src_set = 'srcset, data-srcset';
        $this->log_unprocessable = true;
    }

    public static function getCommaSeparated($key, $default = null)
    {
        $value = static::getIgnoreEmpty($key, $default);

        return explode(',', preg_replace('/\s+/', '', $value));
    }

    public static function getIgnoreEmpty($key, $default = null)
    {
        $value = static::get($key, $default);

        return $value === '' ? $default : $value;
    }

    public function afterSave()
    {
        try {
            $htaccess = new HtaccessManager();
            $htaccess->toggleSection('webp-rewrite', (bool)$this->get('webp_enabled'));
            $htaccess->save();
        } catch (\Throwable $e) {
            logger()->error(
                '[OFFLINE.ResponsiveImages] Failed to automatically enable WebP support using .htaccess',
                ['exeption' => $e]
            );
            Flash::warning('Failed to enable WebP support using .htaccess. You have to manually configure your server!');
        }
    }
}
