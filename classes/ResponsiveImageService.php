<?php

namespace OFFLINE\ResponsiveImages\Classes;

/**
 * Class ResponsiveImageService
 *
 * @package OFFLINE\ResponsiveImages\Classes
 */
class ResponsiveImageService
{
    /**
     * @var string
     */
    private $html;
    /**
     * @var DomManipulator
     */
    private $domManipulator;

    /**
     * @param $html
     */
    public function __construct($html)
    {
        $this->html           = $html;
        $this->domManipulator = new DomManipulator($this->html);
    }

    /**
     * Add srcset and sizes attributes to all images.
     *
     * @return string
     */
    public function process()
    {
        $srcSets = [];

        foreach ($this->domManipulator->getImageSources() as $source) {
            try {
                $responsiveImage = new ResponsiveImage($source);
            } catch (\Exception $e) {
                // Ignore unprocessable images.
                continue;
            }

            $srcSets[$source] = $responsiveImage->getSourceSet();
        }

        return $this->domManipulator->addSrcSetAttributes($srcSets);
    }


}