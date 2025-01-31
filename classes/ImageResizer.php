<?php

namespace OFFLINE\ResponsiveImages\Classes;

use October\Rain\Database\Attach\Resizer as ResizerOCV1;
use October\Rain\Resize\Resizer as ResizerOCV2;
use Winter\Storm\Database\Attach\Resizer as ResizerWN;

if (class_exists(ResizerWN::class)) {
    class Resizer extends ResizerWN { }
} else if (class_exists(ResizerOCV2::class)) {
    class Resizer extends ResizerOCV2 { }
} else {
    class Resizer extends ResizerOCV1 { }
}

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
        if (method_exists(get_parent_class($this), 'getWidth')) {
            return parent::getWidth();
        }

        return $this->width;
    }
}
