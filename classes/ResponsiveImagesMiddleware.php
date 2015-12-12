<?php

namespace OFFLINE\ResponsiveImages\Classes;

use Closure;

/**
 * Class ResponsiveImagesMiddleware
 *
 * @package OFFLINE\ResponsiveImages\Classes
 */
class ResponsiveImagesMiddleware
{

    /**
     * Add srcset and sizes attributes to all local
     * images and create the various image sizes.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Ignore non-string and backend responses.
        if ( ! is_string($response->original) || $this->isBackendRequest($request)) {
            return $response;
        }

        $response->setContent(
            (new ResponsiveImageService($response->original))->process()
        );

        return $response;

    }

    /**
     * @param $request
     *
     * @return bool
     */
    private function isBackendRequest($request)
    {
        return starts_with($request->getRequestUri(), \Config::get('cms.backendUri', '/backend'));
    }

}