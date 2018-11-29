<?php

namespace OFFLINE\ResponsiveImages\Classes;


use OFFLINE\ResponsiveImages\Models\Settings;

class DomManipulatorSettings
{
    /**
     * Attribute to take the image path from.
     *
     * @var string
     */
    public $sourceAttribute = 'src';

    /**
     * Attribute to replace.
     *
     * @var string
     */
    public $targetAttribute = 'srcset';

    /**
     * Class to add.
     *
     * @var string
     */
    public $class = '';

    /**
     * Whether to log unprocessable images or not.
     *
     * @var boolean
     */
    public $logErrors = true;

    /**
     * Create an instance from a SettingsModel class.
     *
     * @param Settings $model
     *
     * @return DomManipulatorSettings
     */
    public static function fromSettingsModel(Settings $model): DomManipulatorSettings
    {
        $settings                  = new self();
        $settings->logErrors       = (bool)$model->get('log_unprocessable', false);
        $settings->sourceAttribute = $model->get('alternative_src') ?: false;
        $settings->targetAttribute = $model->get('alternative_src_set') ?: 'srcset';
        $settings->class           = $model->get('add_class') ?: '';

        return $settings;
    }
}