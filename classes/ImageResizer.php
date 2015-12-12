<?php

namespace OFFLINE\ResponsiveImages\Classes;

use October\Rain\Database\Attach\Resizer;

/**
 * Class ImageResizer
 * @package OFFLINE\ResponsiveImages\Classes
 */
class ImageResizer extends Resizer
{
    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }
}