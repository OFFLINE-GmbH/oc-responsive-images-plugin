<?php

namespace OFFLINE\ResponsiveImages\FormWidgets;


use Backend\FormWidgets\FileUpload;
use October\Rain\Support\Facades\Url;

class FocusPointFileUpload extends FileUpload
{

    public function loadAssets()
    {
        parent::loadAssets();

        $this->addJs('/plugins/offline/responsiveimages/assets/js/focuspoint-tool.js');
    }

    public function getConfigFormWidget()
    {
        if ($this->configFormWidget) {
            return $this->configFormWidget;
        }

        $config = $this->makeConfig('~/plugins/offline/responsiveimages/formwidgets/fields.yaml');
        $config->model = $this->getFileRecord() ?: $this->getRelationModel();
        $config->alias = $this->alias . $this->defaultAlias;
        $config->arrayName = 'FileUploadWidget';

        $widget = $this->makeWidget(\Backend\Widgets\Form::class, $config);
        $widget->bindToController();

        return $this->configFormWidget = $widget;
    }


    public function guessViewPath($suffix = '', $isPublic = false)
    {
        if ($isPublic) {
            return Url::to('/modules/backend/formwidgets/fileupload/' . $suffix);
        }

        return '~/modules/backend/formwidgets/fileupload/' . $suffix;
    }
}
