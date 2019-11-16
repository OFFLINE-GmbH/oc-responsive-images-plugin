<?php

namespace OFFLINE\ResponsiveImages\Classes;


use Illuminate\Support\Facades\URL;
use OFFLINE\ResponsiveImages\Models\Settings;

class WebP
{
    /**
     * @var boolean
     */
    protected $enabled = false;
    /**
     * @var string
     */
    protected $prefix;
    /**
     * The site's base URL.
     * @var string
     */
    protected $base;

    public function __construct()
    {
        $this->enabled = (bool)Settings::get('webp_enabled', false);
        $this->prefix  = Settings::getIgnoreEmpty('webp_prefix', Settings::DEFAULT_WEBP_PREFIX);
        $this->base    = URL::to('/');
    }

    public function prefix(string $url)
    {
        if ( ! $this->enabled) {
            return $url;
        }

        // Make sure no WebP URL includes the app's base URL.
        $stripped = str_replace($this->base, '', $url);

        // This seems to be an external image, don't touch it!
        if (starts_with($stripped, 'http')) {
            return $url;
        }

        $url = URL::to($this->prefix) . rawurlencode($stripped);

        // Bring encoded colon and slashes back
        return str_replace(['%2F', '%3A'], ['/', ':'], $url);
    }
}