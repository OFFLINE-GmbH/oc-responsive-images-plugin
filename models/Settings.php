<?php namespace OFFLINE\ResponsiveImages\Models;

use Model;
use OFFLINE\ResponsiveImages\Classes\Htaccess\Writer;

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
            $htaccess = new Writer();
            if ((bool)$this->get('webp_enabled') === true) {
                $htaccess->addWhitelist();
            }
            if ((bool)$this->get('webp_enabled') === false) {
                $htaccess->removeWhitelist();
            }
            $htaccess->write();
        } catch (\Throwable $e) {
            logger()->error(
                '[OFFLINE.ResponsiveImages] Failed to automatically add webp.php whitelist entry to .htaccess',
                ['exeption' => $e]
            );
        }
    }
}
