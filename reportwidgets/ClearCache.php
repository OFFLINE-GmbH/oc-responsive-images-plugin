<?php namespace OFFLINE\ResponsiveImages\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Exception;

use Artisan;
use Flash;
use Lang;

/**
 * ClearCache Report Widget
 */
class ClearCache extends ReportWidgetBase
{
    /**
     * @inheritDoc
     */
    protected $defaultAlias = 'ClearCacheReportWidget';

    /**
     * defineProperties for the widget
     */
    public function defineProperties()
    {
        return [
            'title' => [
                'title' => 'backend::lang.dashboard.widget_title_label',
                'default' => 'Clear image cache',
                'type' => 'string',
                'validationPattern' => '^.+$',
                'validationMessage' => 'backend::lang.dashboard.widget_title_error',
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        try {
            $this->prepareVars();
        }
        catch (Exception $ex) {
            $this->vars['error'] = $ex->getMessage();
        }

        return $this->makePartial('clearcache');
    }

    public function onClearCache() {
        Artisan::call('responsive-images:clear');
        Flash::success(Lang::get('offline.responsiveimages::lang.reportwidgets.clearcache.success'));
    }

    /**
     * Prepares the report widget view data
     */
    public function prepareVars()
    {
    }

}
