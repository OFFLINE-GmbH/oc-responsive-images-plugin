<?php

namespace OFFLINE\ResponsiveImages\Classes;

use Exception;
use Illuminate\Support\Facades\Log;
use OFFLINE\ResponsiveImages\Classes\Exceptions\FileNotFoundException;
use OFFLINE\ResponsiveImages\Classes\Exceptions\RemotePathException;
use OFFLINE\ResponsiveImages\Classes\Exceptions\UnallowedFileTypeException;
use OFFLINE\ResponsiveImages\Models\Settings;

/**
 * Class ResponsiveImageService
 *
 * @package OFFLINE\ResponsiveImages\Classes
 */
class ResponsiveImageService
{
    /**
     * @var string
     */
    private $html;
    /**
     * @var DomManipulator
     */
    private $domManipulator;
    /**
     * @var boolean
     */
    private $logErrors;

    /**
     * @param $html
     */
    public function __construct($html)
    {
        $this->html           = $html;
        $this->domManipulator = new DomManipulator($this->html);
        $this->logErrors      = Settings::get('log_unprocessable', false);
    }

    /**
     * Add srcset and sizes attributes to all images.
     *
     * @return string
     */
    public function process()
    {
        $srcSets = [];

        foreach ($this->domManipulator->getImageSources() as $source) {
            try {
                $responsiveImage = new ResponsiveImage($source);
            } catch (RemotePathException $e) {
                // Ignore remote images completely
                continue;
            } catch (UnallowedFileTypeException $e) {
                // Ignore file types that are not allowed
                continue;
            } catch (FileNotFoundException $e) {
                $this->log(sprintf('Image %s does not exist', $source), $e);
                continue;
            } catch (Exception $e) {
                $this->log(sprintf('Could not process image %s', $source), $e, true);
                continue;
            }
            $srcSets[$source] = $responsiveImage->getSourceSet();
        }

        return $this->domManipulator->addSrcSetAttributes($srcSets);
    }

    private function log($message, $exception, $forceLogEntry = false)
    {
        if($this->logErrors || $forceLogEntry) {
            Log::warning(sprintf('[OFFLINE.ResponsiveImages] %s', $message), compact('exception'));
        }
    }


}