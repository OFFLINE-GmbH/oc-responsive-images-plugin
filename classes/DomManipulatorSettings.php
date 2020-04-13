<?php

namespace OFFLINE\ResponsiveImages\Classes;


use OFFLINE\ResponsiveImages\Models\Settings;

class DomManipulatorSettings
{
    /**
     * Attribute to take the image path from.
     *
     * @var array
     */
    public $sourceAttribute = ['src'];

    /**
     * Attribute to replace.
     *
     * @var array
     */
    public $targetAttribute = ['srcset'];

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
     * Specific class for focus point images
     *
     * @var boolean
     */
    public $focuspointClass;

    /**
     * Container class for focus point image.
     *
     * @var boolean
     */
    public $focuspointContainerClass;

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
     * Serve images in WebP format.
     *
     * @var boolean
     */
    public $webPEnabled = false;

    /**
     * Create an instance from a SettingsModel class.
     *
     * @param Settings $model
     *
     * @return DomManipulatorSettings
     */
    public static function fromSettingsModel(Settings $model): DomManipulatorSettings
    {
        $split = function($input): array {
            $parts = explode(',', $input);

            return array_filter(array_map('trim', $parts));
        };

        $settings                              = new self();
        $settings->logErrors                   = (bool)$model->get('log_unprocessable', false);
        $settings->sourceAttribute             = $split($model->get('alternative_src') ?: false);
        $settings->targetAttribute             = $split($model->get('alternative_src_set') ?: 'srcset');
        $settings->class                       = $model->get('add_class') ?: '';
        $settings->webPEnabled                 = (bool)$model->get('webp_enabled') ?: false;
        $settings->focuspointClass             = $model->get('focuspoint_class') ?: 'focuspoint-image';
        $settings->focuspointContainerClass    = $model->get('focuspoint_container_class') ?: '';
        $settings->focuspointDataX             = $model->get('focuspoint_data_x') ?: '';
        $settings->focuspointDataY             = $model->get('focuspoint_data_y') ?: '';
        $settings->focuspointAllowInlineObject = (bool)$model->get('allow_inline_object', true);
        $settings->focuspointAllowInlineSizing = (bool)$model->get('allow_inline_sizing', true);

        return $settings;
    }
}