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
        // This call is needed since Build 370 when the new getWidth
        // method was introduced and conflicted with this implementation.
        // We can now use the parent's getWidth method if it is available.
        // For older versions of october the width property gets returned.
        if (is_callable('parent::getWidth')) {
            return parent::getWidth();
        }

        return $this->width;
    }
}