<?php

namespace OFFLINE\ResponsiveImages\Tests;

use OFFLINE\ResponsiveImages\Classes\DomManipulator;
use PluginTestCase;

class DomManipulatorTest extends PluginTestCase
{
    public function testGetImageSources()
    {
        $manipulator = new DomManipulator('<html><img src="test.jpg"><img src="test2.jpg">');
        $sources     = $manipulator->getImageSources();

        $this->assertCount(2, $sources);
        $this->assertEquals(['test.jpg', 'test2.jpg'], $sources);
    }
}

