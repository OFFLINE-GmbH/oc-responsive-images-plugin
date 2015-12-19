<?php

namespace OFFLINE\ResponsiveImages\Tests;

use Illuminate\Support\Facades\URL;
use OFFLINE\ResponsiveImages\Classes\SourceSet;
use PluginTestCase;

class SourceSetTest extends PluginTestCase
{
    public function testAddRule()
    {
        $srcset = $this->createSourceSet();

        $expected = [
            200 => [
                'storage_path' => '/path.jpg',
                'public_url'   => URL::to('/') . '/path.jpg',
            ],
            300 => [
                'storage_path' => '/path2.jpg',
                'public_url'   => URL::to('/') . '/path2.jpg',
            ],
        ];

        $this->assertEquals($expected, $srcset->rules);
    }

    public function testRemoveRule()
    {
        $srcset = $this->createSourceSet();

        $srcset->remove(200);

        $expected = [
            300 => [
                'storage_path' => '/path2.jpg',
                'public_url'   => URL::to('/') . '/path2.jpg',
            ],
        ];

        $this->assertEquals($expected, $srcset->rules);
    }

    public function testGetSourceSetAttribute()
    {
        $srcset = $this->createSourceSet();

        $base     = URL::to('/');
        $expected = "${base}/path.jpg 200w, ${base}/path2.jpg 300w";

        $this->assertEquals($expected, $srcset->getSrcSetAttribute());
    }

    public function testGetSizesAttribute()
    {
        $srcset = $this->createSourceSet();

        $expected = '(max-width: 200px) 100vw, 200px';

        $this->assertEquals($expected, $srcset->getSizesAttribute(200));
    }

    /**
     * @return SourceSet
     */
    protected function createSourceSet()
    {
        $srcset = new SourceSet('/path.jpg', 200);

        $this->assertCount(1, $srcset->rules);

        $srcset->push(300, '/path2.jpg');
        $this->assertCount(2, $srcset->rules);

        return $srcset;
    }
}

