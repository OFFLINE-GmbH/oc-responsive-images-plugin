<?php

namespace OFFLINE\ResponsiveImages\Classes;

use Illuminate\Support\Facades\Log;

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
                $source = urldecode($source);
                $responsiveImage = new ResponsiveImage($source);
            } catch (\Exception $e) {
                // we should log what kind of images are not processable and then continue loop
                Log::warning("[Offline.responsiveimages] could not process image: " . $source);
                continue;
            }

            $srcSets[$source] = $responsiveImage->getSourceSet();
        }

        return $this->domManipulator->addSrcSetAttributes($srcSets);
    }


}