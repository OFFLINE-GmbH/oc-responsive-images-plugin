<?php

namespace OFFLINE\ResponsiveImages\Classes;

use Config;
use DOMDocument;
use DOMElement;
use OFFLINE\ResponsiveImages\Classes\Exceptions\FileNotFoundException;
use OFFLINE\ResponsiveImages\Classes\Exceptions\RemotePathException;
use OFFLINE\ResponsiveImages\Classes\Exceptions\UnallowedFileTypeException;
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
    protected $pattern = '/<img[^>]*(?:\s(?:%s))=[\s\S]*?>/mis';
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
            sprintf($this->pattern, $this->getSrcAttributeNameForRegex()),
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
            $node = $this->loadImageTag($matches[0]);
            // The node could not be parsed. Return the original match.
            if ($node === false) {
                return $matches[0];
            }

            // This image should explicitly be ignored, so return the original tag.
            if ($node->getAttribute('data-responsive') === 'ignore') {
                return $matches[0];
            }

            $source = $this->getSrcAttribute($node);

            $responsiveImage = $this->getResponsiveImage($source->url);
            if ($responsiveImage === null) {
                // The processing of the image failed return original tag.
                return $matches[0];
            }

            $sourceSet = $responsiveImage->getSourceSet();
            $this->setSrcSetAttribute($node, $sourceSet, $source->target);
            $this->setSizesAttribute($node, $sourceSet);
            $this->setClassAttribute($node);

            // If it's an Image with a focuspoint add additional properties.
            if (strpos($source->url, 'offline-focus')) {
                $filename = substr($source->url, strpos( $source->url, 'offline-focus'));

                $sourceAttributes = explode('_', $filename);

                $focusImageValues = [
                    'width'  => $sourceAttributes[2],
                    'height' => $sourceAttributes[3],
                    'x'      => $sourceAttributes[4],
                    'y'      => $sourceAttributes[5],
                ];

                $node = $this->focuspointImage($node, $focusImageValues, $this->settings);
            }

            $this->setSrcAttribute($node, $sourceSet, $source->src);

            return $node->ownerDocument->saveHTML($node);
        };
    }

    /**
     * Loads a single img tag into the DOMDocument.
     *
     * @param string $tag
     *
     * @return DOMElement|boolean
     */
    protected function loadImageTag(string $tag)
    {
        try {
            // Try and fix invalid XML. If an img source contains unescaped symbols like &
            // the DOMDocument will not be able to parse the markup. This is why we
            // encode the whole tag and then convert the < and > entities back.
            // Not pretty, but it works.
            $cleanedTag = mb_convert_encoding($tag, 'HTML-ENTITIES', 'UTF-8');
            $cleanedTag = htmlspecialchars($cleanedTag, ENT_NOQUOTES, 'UTF-8', false);
            $cleanedTag = str_replace('&lt;', '<', $cleanedTag);
            $cleanedTag = str_replace('&gt;', '>', $cleanedTag);

            $this->dom->loadHTML($cleanedTag);

            return $this->dom->getElementsByTagName('img')->item(0);
        } catch (\Throwable $e) {
            $this->log(sprintf('Failed to parse tag. HTML might contain errors: %s', $tag), $e);
        }

        return false;
    }

    /**
     * Build a ResponsiveImage instance from $source.
     *
     * @param $source
     *
     * @return ResponsiveImage|null
     */
    protected function getResponsiveImage(string $source)
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
     * @param DOMElement $node
     * @param SourceSet  $sourceSet
     * @param string     $targetAttribute
     */
    protected function setSrcSetAttribute(DOMElement $node, SourceSet $sourceSet, string $targetAttribute)
    {
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
     * @param DOMElement $node
     * @param SourceSet  $sourceSet
     * @param string     $srcAttr
     */
    protected function setSrcAttribute(DOMElement $node, SourceSet $sourceSet, string $srcAttr)
    {
        $node->setAttribute($srcAttr, $sourceSet->getSrcAttribute());
    }

    /**
     * Returns all configured src attributes to use in a regex.
     * @return string
     */
    protected function getSrcAttributeNameForRegex(): string
    {
        if (count($this->settings->sourceAttribute) < 1) {
            return 'src';
        }

        return implode('|', $this->settings->sourceAttribute);
    }

    /**
     * Normalize the image's src attribute and return it.
     *
     * @param $node
     *
     * @return ImageSource
     */
    protected function getSrcAttribute($node): ImageSource
    {
        $sourceAttr = 'src';
        $targetAttr = 'srcset';

        // Use the src attribute by default.
        $src = $node->getAttribute($sourceAttr);

        // If alternative sources are available, check if the
        // $node has them defined and use the first one
        // that is available.
        $altSources = $this->settings->sourceAttribute;
        foreach ($altSources as $index => $altSrc) {
            if ($altSrc && $node->getAttribute($altSrc) !== '') {
                $src = $node->getAttribute($altSrc);
                $sourceAttr = $altSrc;
                $targetAttr = $this->getTargetAttrAtIndex($index);
                break;
            }
        }

        // If the protocol is missing from the URL prepend it.
        // It doesn't matter if it matches the actual protocol since it will
        // be striped out later anyway. We just need it to make sure the
        // parsing of the image path works as intended.
        if (starts_with($src, '//')) {
            $src = 'http:' . $src;
        }

        $src = trim($src, '/');

        return ImageSource::make($node, $src, $sourceAttr, $targetAttr);
    }

    /**
     * Get the configured target attribute for a certain src attribute.
     *
     * @param $index
     *
     * @return string
     */
    protected function getTargetAttrAtIndex($index): string
    {
        return $this->settings->targetAttribute[$index] ?? 'data-no-matching-target-configured';
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
            $stylingAttributes[] = 'width: 100%';
            $stylingAttributes[] = 'height: 100%';
        }

        if ($settings->focuspointAllowInlineObject) {
            $stylingAttributes[] = sprintf('object-position: %s%% %s%%', $x, $y);
            $stylingAttributes[] = 'object-fit: cover';
        }

        if ($stylingAttributes) {
            $node->setAttribute('style', implode(';', $stylingAttributes));
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
