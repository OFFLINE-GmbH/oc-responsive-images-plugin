<?php

namespace OFFLINE\ResponsiveImages\Classes\Convert;

use OFFLINE\ResponsiveImages\Models\Settings;

class PathProcessorOptions
{
    /**
     * @var string
     */
    private $format;
    /**
     * @var string
     */
    private $size;
    /**
     * @var int
     */
    private $since;

    public function __construct(string $format, string $size, string $since = null)
    {
        $this->format = $format;
        $this->size = $size;
        $this->since = $since ? strtotime($since) : Settings::get('latest_conversion', '0');
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @return string
     */
    public function getSince(): string
    {
        return $this->since;
    }

    /**
     * @return string
     */
    public function getSize(): string
    {
        return $this->size;
    }

    /**
     * @return array
     */
    public function getAllowedExtensions()
    {
        return Settings::getCommaSeparated('allowed_extensions');
    }
}
