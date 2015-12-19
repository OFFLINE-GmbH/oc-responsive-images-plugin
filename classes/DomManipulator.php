<?php

namespace OFFLINE\ResponsiveImages\Classes;

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

        $this->dom->loadHTML($html);
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
    public function addSrcSetAttributes(array $srcSets, ResponsiveImage $responsiveImage)
    {
        foreach ($this->imgNodes as $node) {
            if ( ! array_key_exists($this->getSrcAttribute($node), $srcSets)) {
                // There are no alternative sizes available for this image
                continue;
            }

            $this->setSrcSetAttribute($node, $srcSets[$this->getSrcAttribute($node)]);
            $this->setSizesAttribute($node, $responsiveImage);
        }

        return $this->dom->saveHTML();
    }

    /**
     * Set the sizes attribute based on the image's width attribute.
     *
     * @param $node
     */
    protected function setSizesAttribute($node, ResponsiveImage $responsiveImage)
    {
        // Don't overwrite existing attributes
        if ($node->getAttribute('sizes') !== '') {
            return;
        }

        if ($width = $node->getAttribute('width') === '') {
            $width = $responsiveImage->getOriginalWidth();
        }

        $sizes = $width !== ''
            ? sprintf('(max-width: %1$dpx) 100vw, %1$dpx', $width)
            : '100vw';

        $node->setAttribute('sizes', $sizes);
    }

    /**
     * Set the srcset attribute.
     *
     * @param $node
     * @param $srcSets
     */
    protected function setSrcSetAttribute($node, $srcSets)
    {
        // Don't overwrite existing attributes
        if ($node->getAttribute('srcset') !== '') {
            return;
        }

        $attribute = [];

        foreach ($srcSets as $size => $url) {
            $attribute[] = sprintf('%s %sw', $url, $size);
        }

        $node->setAttribute('srcset', implode($attribute, ', '));
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
        return trim($node->getAttribute('src'), '/');
    }

}