<?php

namespace OFFLINE\ResponsiveImages\Classes;

use Config;

/**
 * Manipulates images in a DOMDocument.
 *
 * @package OFFLINE\ResponsiveImages\Classes
 */
class DomManipulator
{
    /**
     * @var \DOMNodeList
     */
    public $imgNodes;

    /**
     * Loads the html.
     *
     * @param                   $html
     * @param \DOMDocument|null $dom
     */
    public function __construct($html, \DOMDocument $dom = null)
    {
        // suppress errors in case of invalid html
        libxml_use_internal_errors(true);

        if ($dom === null) {
            $this->dom = new \DOMDocument;
        }

        $this->dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $this->imgNodes = (new \DOMXPath($this->dom))->query("//img");
    }

    /**
     * Returns an array of all img src attributes.
     *
     * @return array
     */
    public function getImageSources()
    {
        $images = [];

        foreach ($this->imgNodes as $node) {
            $images[] = $this->getSrcAttribute($node);
        }

        return $images;
    }

    /**
     * Adds srcset and sizes attributes to all local images
     * in the DOMDocument.
     *
     * @param $srcSets
     *
     * @return string
     */
    public function addSrcSetAttributes(array $srcSets)
    {
        foreach ($this->imgNodes as $node) {

            $src = $this->getSrcAttribute($node);

            if ( ! array_key_exists($src, $srcSets)) {
                // There are no alternative sizes available for this image
                continue;
            }

            $this->setSrcSetAttribute($node, $srcSets[$src]);
            $this->setSizesAttribute($node, $srcSets[$src]);
        }

        return $this->dom->saveHTML();
    }

    /**
     * Set the sizes attribute based on the image's width attribute.
     *
     * @param           $node
     * @param SourceSet $sourceSet
     */
    protected function setSizesAttribute($node, SourceSet $sourceSet)
    {
        // Don't overwrite existing attributes
        if ($node->getAttribute('sizes') !== '') {
            return;
        }

        $node->setAttribute('sizes', $sourceSet->getSizesAttribute($node->getAttribute('width')));
    }

    /**
     * Set the srcset attribute.
     *
     * @param $node
     * @param $sourceSet
     */
    protected function setSrcSetAttribute($node, SourceSet $sourceSet)
    {
        // Don't overwrite existing attributes
        if ($node->getAttribute('srcset') !== '') {
            return;
        }

        $node->setAttribute('srcset', $sourceSet->getSrcSetAttribute());
    }

    /**
     * Normalize the image's src attribute and return it.
     *
     * @param $node
     *
     * @return mixed
     */
    protected function getSrcAttribute($node)
    {
        $src = urldecode( $node->getAttribute('src') );

        $altSrc = Config::get('offline.responsiveimages::alternative-src', false);

        if ($altSrc && $node->getAttribute($altSrc) !== '') {
            $src = $node->getAttribute($altSrc);
        }

        return trim($src, '/');
    }

}