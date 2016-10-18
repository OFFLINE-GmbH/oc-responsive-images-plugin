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
                $responsiveImage = new ResponsiveImage($source);
            } catch(\RemotePathException $e) {
                //we dont want to log all remote images so just continue here
                continue;
            } catch (\Exception $e) {
                Log::warning("[Offline.responsiveimages] could not process image: " . $source);
                continue;
            }

            $srcSets[$source] = $responsiveImage->getSourceSet();
        }

        return $this->domManipulator->addSrcSetAttributes($srcSets);
    }


}