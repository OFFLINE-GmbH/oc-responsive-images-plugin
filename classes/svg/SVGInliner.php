<?php

namespace OFFLINE\ResponsiveImages\Classes\SVG;


use October\Rain\Parse\Twig;

class SVGInliner
{
    /**
     * @var string
     */
    protected $themeDir;

    /**
     * SVG to be returned when requested file is not found.
     * @var string
     */
    protected $notFoundSvg = <<<EOL
<svg data-error="[OFFLINE.ResponsiveImages] Inline SVG '%s' not found!"
    style="width: 20px; fill: currentColor"
    xmlns="http://www.w3.org/2000/svg" 
    viewBox="0 0 60 60">
        <path d="M0 0v60h60V0H0zm58 58H2V2h56v56z"/>
        <path fill="#f00" d="M16.009 45.405l14.142-14.142 14.142 14.142 1.414-1.414-14.142-14.142 14.142-14.142-1.414-1.414-14.142 14.142-14.142-14.142-1.414 1.414 14.142 14.142-14.142 14.142z"/>
</svg>
EOL;

    public function __construct(string $themeDir)
    {
        $this->themeDir = $themeDir;
    }

    public function inline(string $relPath, array $vars)
    {
        // If the path starts with a slash search relative to the base path. If
        // it starts with the app's host name make it relative.
        $appUrl = trim(config('app.url'), '/');
        if (starts_with($relPath, $appUrl)) {
            $relPath = str_replace($appUrl, '', $relPath);
        }

        // If the path starts with a slash search relative to the base path.
        if (starts_with($relPath, '/')) {
            $path = base_path($relPath);
        } else {
            $path = themes_path(implode('/', [$this->themeDir, $relPath]));
        }

        if ( ! file_exists($path)) {
            logger()->warning(
                sprintf(
                    '[OFFLINE.ResponsiveImages] Cannot inline svg %s. Not found in %s.',
                    $relPath,
                    $path
                )
            );

            return sprintf($this->notFoundSvg, $relPath);
        }

        $contents = file_get_contents($path);

        return $this->replaceVars($contents, $vars);
    }

    protected function replaceVars(string $contents, array $vars = [])
    {
        return (new Twig)->parse($contents, $vars);
    }
}