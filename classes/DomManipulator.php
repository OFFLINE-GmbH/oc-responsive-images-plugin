<?php

namespace OFFLINE\ResponsiveImages\Classes;

use Config;
use DOMDocument;
use DOMElement;
use OFFLINE\ResponsiveImages\Classes\Exceptions\FileNotFoundException;
use OFFLINE\ResponsiveImages\Classes\Exceptions\RemotePathException;
use OFFLINE\ResponsiveImages\Classes\Exceptions\UnallowedFileTypeException;
use OpenCloud\Common\Exceptions\DomainError;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Replaces all images in the HTML document.
 *
 * @package OFFLINE\ResponsiveImages\Classes
 */
class DomManipulator
{
    /**
     * @var string
     */
    public $html;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * RegEx to find all images in the document.
     * @var string
     */
    protected $pattern = '/<img[\s\S][^>]?(?:src)=[\s\S]*?>/mis';
    /**
     * DOMDocument instance to process each img tag.
     * @var DOMDocument
     */
    protected $dom;
    /**
     * @var DomManipulatorSettings
     */
    protected $settings;

    /**
     * Loads the html.
     *
     * @param                      $html
     * @param LoggerInterface|null $logger
     */
    public function __construct($html, DomManipulatorSettings $settings, LoggerInterface $logger = null)
    {
        $this->html     = $html;
        $this->logger   = $logger ?? new NullLogger();
        $this->settings = $settings;
        $this->dom      = new DOMDocument();
    }

    /**
     * Returns the processed html document.
     *
     * @return string
     */
    public function process(): string
    {
        return preg_replace_callback(
            $this->pattern,
            $this->replaceCallback(),
            $this->html
        );
    }

    /**
     * Set sizes and srcset attributes for every single image tag.
     *
     * @return \Closure
     */
    protected function replaceCallback()
    {
        return function ($matches) {
            $node   = $this->loadImageTag($matches[0]);
            $source = $this->getSrcAttribute($node);

            $responsiveImage = $this->getResponsiveImage($source);
            if ($responsiveImage === null) {
                // The processing of the image failed return original tag.
                return $matches[0];
            }

            $sourceSet = $responsiveImage->getSourceSet();
            $this->setSrcSetAttribute($node, $sourceSet);
            $this->setSizesAttribute($node, $sourceSet);
            $this->setClassAttribute($node);

            // If it's an Image with a focuspoint add additional properties.
            if (strpos($source, 'offline-focus')) {
                $sourceAttributes = explode('_', $this->getSrcAttribute($node));

                $focusImageValues = [
                    'width'  => $sourceAttributes[2],
                    'height' => $sourceAttributes[3],
                    'x'      => $sourceAttributes[4],
                    'y'      => $sourceAttributes[5],
                ];

                $node = $this->focuspointImage($node, $focusImageValues, $this->settings);
            }

            return $node->ownerDocument->saveHTML($node);
        };
    }

    /**
     * Loads a single img tag into the DOMDocument.
     *
     * @param string $tag
     *
     * @return DOMElement
     */
    protected function loadImageTag(string $tag): DOMElement
    {
        $this->dom->loadHTML(mb_convert_encoding($tag, 'HTML-ENTITIES', 'UTF-8'));

        return $this->dom->getElementsByTagName('img')->item(0);
    }

    /**
     * Build a ResponsiveImage instance from $source.
     *
     * @param $source
     *
     * @return ResponsiveImage|null
     */
    protected function getResponsiveImage($source)
    {
        try {
            return new ResponsiveImage($source);
        } catch (RemotePathException $e) {
            // Ignore remote images completely
        } catch (UnallowedFileTypeException $e) {
            // Ignore file types that are not allowed
        } catch (FileNotFoundException $e) {
            $this->log(sprintf('Image %s does not exist', $source), $e);
        } catch (\Throwable $e) {
            $this->log(sprintf('Could not process image %s', $source), $e, true);
        }

        return null;
    }

    /**
     * Set the sizes attribute based on the image's width attribute.
     *
     * @param           $node
     * @param SourceSet $sourceSet
     */
    protected function setSizesAttribute(DOMElement $node, SourceSet $sourceSet)
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
    protected function setSrcSetAttribute(DOMElement $node, SourceSet $sourceSet)
    {
        $targetAttribute = $this->settings->targetAttribute;

        // Don't overwrite existing attributes
        if ($node->getAttribute($targetAttribute) !== '') {
            return;
        }

        $node->setAttribute($targetAttribute, $sourceSet->getSrcSetAttribute());
    }

    /**
     * Set the class attribute.
     *
     * @param $node
     */
    protected function setClassAttribute(DOMElement $node)
    {
        if ( ! $class = $this->settings->class) {
            return;
        }

        $classes = $node->getAttribute('class');

        $node->setAttribute('class', "$classes $class");
    }

    /**
     * Set the images's src attribute.
     *
     * @param $node
     * @param $sourceSet
     */
    protected function setSrcAttribute(DOMElement $node, SourceSet $sourceSet)
    {
        $node->setAttribute('src', $sourceSet->getSrcAttribute());
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
        $src = $node->getAttribute('src');

        $altSrc = $this->settings->sourceAttribute;

        if ($altSrc && $node->getAttribute($altSrc) !== '') {
            $src = $node->getAttribute($altSrc);
        }

        // If the protocol is missing from the URL prepend it.
        // It doesn't matter if it matches the actual protocol since it will
        // be striped out later anyway. We just need it to make sure the
        // parsing of the image path works as intended.
        if (starts_with($src, '//')) {
            $src = 'http:' . $src;
        }

        return trim($src, '/');
    }

    /**
     * Log an error message.
     *
     * @param      $message
     * @param      $exception
     * @param bool $forceLogEntry
     */
    protected function log($message, $exception, $forceLogEntry = false)
    {
        if ($this->settings->logErrors || $forceLogEntry) {
            $this->logger->warning(
                sprintf('[OFFLINE.ResponsiveImages] %s', $message),
                compact('exception')
            );
        }
    }

    /**
     * Set Focuspoint Attributes based on the settings.
     *
     * @param $node
     * @param $attributes
     * @param $settings
     *
     * @return DOMElement
     */
    protected function focuspointImage(
        DOMElement $node,
        array $attributes,
        DomManipulatorSettings $settings
    ): DOMElement {
        $classes = $node->getAttribute('class');

        $x = $attributes['x'] === '' ? 50 : $attributes['x'];
        $y = $attributes['y'] === '' ? 50 : $attributes['y'];

        $node->setAttribute('class', trim("$classes $settings->focuspointClass"));

        $stylingAttributes = [];

        if ($settings->focuspointAllowInlineSizing) {
            $stylingAttributes[] = sprintf('width: %spx', $attributes['width']);
            $stylingAttributes[] = sprintf('height: %spx', $attributes['height']);
        }

        if ($settings->focuspointAllowInlineObject) {
            $stylingAttributes[] = sprintf('object-position: %s%% %s%%', $x, $y);
            $stylingAttributes[] = 'object-fit: cover';
        }

        if ($stylingAttributes) {
            $node->setAttribute('style', implode($stylingAttributes, '; '));
        }

        // Set data-* attributes on the image to enable use of JS plugins.
        $node = $this->setFocusDataAttributes($node, $settings, $x, $y);

        if ($settings->focuspointContainerClass) {
            $container = $this->dom->createElement('div');
            $container->setAttribute('class', $settings->focuspointContainerClass);
            $container->appendChild($node);

            // Set the data-* attributes on the container as well. There are JS plugins that require it.
            $node = $this->setFocusDataAttributes($container, $settings, $x, $y);
        }

        return $node;
    }

    /**
     * Add data-* Attributes with focus point coordinates.
     *
     * @param DOMElement             $node
     * @param DomManipulatorSettings $settings
     *
     * @param                        $x
     * @param                        $y
     *
     * @return DOMElement
     */
    private function setFocusDataAttributes(DOMElement $node, DomManipulatorSettings $settings, $x, $y): DOMElement
    {
        if ($settings->focuspointDataX) {
            $node->setAttribute('data-' . $settings->focuspointDataX, $x);
        }

        if ($settings->focuspointDataY) {
            $node->setAttribute('data-' . $settings->focuspointDataY, $y);
        }

        return $node;
    }
}
