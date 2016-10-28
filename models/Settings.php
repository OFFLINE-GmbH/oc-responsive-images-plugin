<?php namespace OFFLINE\ResponsiveImages\Models;

use Model;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    // A unique code
    public $settingsCode = 'offline_responsiveimages_settings';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';

    public static function getCommaSeparated($key, $default = null)
    {
        $value = static::get($key, $default);

        if ($value === '') {
            $value = $default;
        }

        return explode(',', preg_replace('/\s+/', '', $value));
    }
}