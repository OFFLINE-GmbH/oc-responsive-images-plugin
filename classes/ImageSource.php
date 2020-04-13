<?php

namespace OFFLINE\ResponsiveImages\Classes;


class ImageSource
{
    /**
     * @var \DOMElement
     */
    public $node;
    /**
     * @var string
     */
    public $src;
    /**
     * @var string
     */
    public $target;
    /**
     * @var string
     */
    public $url;

    public function __construct(\DOMElement $node, string $url, string $src, string $target)
    {
        $this->node = $node;
        $this->src = $src;
        $this->target = $target;
        $this->url = $url;
    }

    public static function make(\DOMElement $node, string $url, string $src, string $target): self {
        return new self($node, $url, $src, $target);
    }
}