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
        $this->since = $since ?: Settings::get('latest_conversion', '1970-01-01');
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @return string|null
     */
    public function getSince()
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
