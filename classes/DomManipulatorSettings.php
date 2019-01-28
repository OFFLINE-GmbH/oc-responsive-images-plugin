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
     * Specific class for Focuspoint-Images
     *
     * @var boolean
     */
    public $focuspointClass;

    /**
     * Data-Attribute for x-axis
     *
     * @var boolean
     */
    public $focuspointDataX;

    /**
     * Data-Attribute for y-axis
     *
     * @var boolean
     */
    public $focuspointDataY;

    /**
     * Allow inline-styles for object-fit and object-position
     *
     * @var boolean
     */
    public $focuspointAllowInlineObject = true;

    /**
     * Allow inline-styles for width and height
     *
     * @var boolean
     */
    public $focuspointAllowInlineSizing = true;

    /**
     * Create an instance from a SettingsModel class.
     *
     * @param Settings $model
     *
     * @return DomManipulatorSettings
     */
    public static function fromSettingsModel(Settings $model): DomManipulatorSettings
    {
        $settings                              = new self();
        $settings->logErrors                   = (bool)$model->get('log_unprocessable', false);
        $settings->sourceAttribute             = $model->get('alternative_src') ?: false;
        $settings->targetAttribute             = $model->get('alternative_src_set') ?: 'srcset';
        $settings->class                       = $model->get('add_class') ?: '';
        $settings->focuspointClass             = $model->get('focuspoint_class') ?: 'focuspoint-image';
        $settings->focuspointDataX             = $model->get('focuspoint_data_x') ?: '';
        $settings->focuspointDataY             = $model->get('focuspoint_data_y') ?: '';
        $settings->focuspointAllowInlineObject = (bool)$model->get('allow_inline_object', false);
        $settings->focuspointAllowInlineSizing = (bool)$model->get('allow_inline_sizing', false);

        return $settings;
    }
}