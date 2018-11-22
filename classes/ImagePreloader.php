<?php

namespace OFFLINE\ResponsiveImages\Classes;


use Backend\Facades\Backend;
use Cms\Classes\Controller;
use Cms\Classes\Theme;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;

/**
 * Class ImagePreloader
 * @package OFFLINE\ResponsiveImages\Classes
 */
class ImagePreloader
{

    protected $theme;

    protected $urls;

    /**
     * ImagePreloader constructor.
     * @param $theme
     */
    public function __construct($theme)
    {
        $this->theme = $theme;
    }

    /**
     * Returns a list of registered item types
     * @return array Returns an array of registered item types
     */
    public function getTypeOptions()
    {
        $result = [];
        $apiResult = Event::fire('pages.menuitem.listTypes');

        if (is_array($apiResult)) {
            foreach ($apiResult as $typeList) {
                if (!is_array($typeList)) {
                    continue;
                }

                foreach ($typeList as $typeCode => $typeName) {
                    $result[$typeCode] = $typeName;
                }
            }
        }

        return $result;
    }

    public function getTypeInfo($type)
    {

        $result = [];
        $apiResult = Event::fire('pages.menuitem.getTypeInfo', [$type]);

        if (is_array($apiResult)) {
            foreach ($apiResult as $typeInfo) {
                if (!is_array($typeInfo)) {
                    continue;
                }

                foreach ($typeInfo as $name => $value) {
                    if ($name == 'cmsPages') {
                        $cmsPages = [];

                        foreach ($value as $page) {
                            $baseName = $page->getBaseFileName();
                            $pos = strrpos($baseName, '/');

                            $dir = $pos !== false ? substr($baseName, 0, $pos) . ' / ' : null;
                            $cmsPages[$baseName] = strlen($page->title)
                                ? $dir . $page->title
                                : $baseName;
                        }

                        $value = $cmsPages;
                    }

                    $result[$name] = $value;
                }
            }
        }

        return $result;

    }

    public function getUrlsForItem($baseUrl, $theme,$type,  $definition, $item)
    {

        $apiResult = Event::fire('pages.menuitem.resolveItem', [$type, $item, $baseUrl, $theme]);

        if (!is_array($apiResult)) {
            return;
        }

        foreach ($apiResult as $itemInfo) {
            if (isset($itemInfo['url'])) {
                $this->urls[] = $itemInfo['url'];
            }

            if (isset($itemInfo['items'])) {

                $parentItem = $item;

                $itemIterator = function ($items) use (&$itemIterator, $parentItem) {
                    foreach ($items as $item) {
                        if (isset($item['url'])) {
                            $this->urls[] = $item['url'];
                        }

                        if (isset($item['items'])) {
                            $itemIterator($item['items']);
                        }
                    }
                };

                $itemIterator($itemInfo['items']);
            }
        }
    }

    public function getPageUrls($definitions)
    {

        $baseUrl = Backend::baseUrl();
        $theme = Theme::load($this->theme);

        $this->urls = [];

        foreach ($definitions as $type => $definition) {

            foreach (data_get($definition, 'references', []) as $reference => $name) {

                if (array_key_exists('cmsPages', $definition)) {
                    foreach ($definition['cmsPages'] as $cmsPage) {
                        $item = (object)[
                            'type' => $type,
                            'reference' => $reference,
                            'cmsPage' => $cmsPage,
                            'nesting' => data_get($definition, 'nesting', false)
                        ];

                        $this->getUrlsForItem($baseUrl, $theme, $type, $definition, $item);
                    }
                } else {
                    $item = (object)[
                        'type' => $type,
                        'reference' => $reference,
                        'nesting' => data_get($definition, 'nesting', false)
                    ];
                    $this->getUrlsForItem($baseUrl, $theme, $type, $definition, $item);
                }

            }

        }

        return $this->urls;
    }

    public function preloadImages()
    {
        $types = $this->getTypeOptions();

        $definitions = [];

        foreach ($types as $type => $name) {
            $definitions[$type] = $this->getTypeInfo($type);
        }

        $urls = $this->getPageUrls($definitions);


        foreach ($urls as $url) {

            $urlParts = parse_url($url);
            $relative = data_get($urlParts,"path","/") .
                (array_key_exists('query', $urlParts) ? '?'. $urlParts['query'] : "") .
                (array_key_exists('fragment', $urlParts) ? '#'. $urlParts['fragment'] : "");

            try {
                $response = App::make(Controller::class)->run($relative);

                (new ResponsiveImageService($response->getContent()))->process();
            }catch (\Exception $e){
            }

        }
    }

}