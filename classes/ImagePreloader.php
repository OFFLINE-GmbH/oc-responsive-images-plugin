<?php

namespace OFFLINE\ResponsiveImages\Classes;


use Backend\Facades\Backend;
use Cms\Classes\Controller;
use Cms\Classes\Theme;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use OFFLINE\ResponsiveImages\Models\Settings;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ImagePreloader
{
    /**
     * The code of the theme to use.
     * @var string
     */
    protected $theme;
    /**
     * Generated URLs.
     * @var Collection
     */
    protected $urls;
    /**
     * The base URL of this installation.
     * @var string
     */
    protected $baseUrl;

    public function __construct($theme, OutputInterface $output = null)
    {
        $this->baseUrl = Backend::baseUrl();
        $this->theme   = Theme::load($theme);
        $this->urls    = collect();
        $this->output  = $output ?? new NullOutput();
    }

    public function preload()
    {
        $definitions = $this->getTypeOptions()->mapWithKeys(function ($name, $type) {
            return [$type => $this->getTypeInfo($type)];
        });

        $urls = $this->getPageUrls($definitions);


        foreach ($urls->sort() as $url) {
            $urlParts = parse_url($url);
            $path     = data_get($urlParts, 'path', '/');
            $query    = array_key_exists('query', $urlParts) ? '?' . $urlParts['query'] : '';
            $fragment = array_key_exists('fragment', $urlParts) ? '#' . $urlParts['fragment'] : '';

            $relative = implode('', [$path, $query, $fragment]);

            try {
                $response = App::make(Controller::class)->run($relative);

                $content = $response->getContent();
                if (empty($content)) {
                    $this->output->writeln("Skipping empty page: \t$relative");
                    continue;
                }

                $this->output->writeln("Processing page: \t$relative");

                (new DomManipulator($content, $this->getSettings(), app('log')))->process();

            } catch (\Exception $e) {
                $this->output->writeln("Failed to process page: \t$relative \t" . $e->getMessage());
            }
        }
    }

    /**
     * Returns a list of registered item types.
     *
     * @return Collection
     */
    public function getTypeOptions(): Collection
    {
        $result    = collect();
        $apiResult = Event::fire('pages.menuitem.listTypes');

        if ( ! \is_array($apiResult)) {
            return $result;
        }

        foreach ($apiResult as $typeList) {
            if ( ! \is_array($typeList)) {
                continue;
            }

            foreach ($typeList as $typeCode => $typeName) {
                $result->put($typeCode, $typeName);
            }
        }

        return $result;
    }

    /**
     * Get all available info for a certain item type.
     *
     * @param $type
     *
     * @return array
     */
    public function getTypeInfo($type): array
    {
        $result    = [];
        $apiResult = Event::fire('pages.menuitem.getTypeInfo', [$type]);

        if ( ! \is_array($apiResult)) {
            return $result;
        }

        foreach ($apiResult as $typeInfo) {
            if ( ! is_array($typeInfo)) {
                continue;
            }

            foreach ($typeInfo as $name => $value) {
                if ($name !== 'cmsPages') {
                    $result[$name] = $value;
                    continue;
                }

                // CMS pages are special
                $cmsPages = [];
                foreach ($value as $page) {
                    $baseName = $page->getBaseFileName();
                    $pos      = strrpos($baseName, '/');

                    $dir                 = $pos !== false ? substr($baseName, 0, $pos) . ' / ' : null;
                    $cmsPages[$baseName] = $page->title !== '' ? $dir . $page->title : $baseName;
                }

                $result[$name] = $cmsPages;
            }
        }

        return $result;

    }

    /**
     * Generate the page URLs for every definition.
     *
     * @param Collection $definitions
     *
     * @return Collection
     */
    public function getPageUrls(Collection $definitions): Collection
    {
        return $definitions->flatMap(function ($definition, $type) {
            $urls       = new Collection();
            $references = data_get($definition, 'references', []);
            foreach ($references as $reference => $name) {
                if ( ! isset($definition['cmsPages'])) {
                    $item = (object)[
                        'type'      => $type,
                        'replace'   => false,
                        'reference' => $reference,
                        'nesting'   => data_get($definition, 'nesting', false),
                    ];
                    $urls = $urls->concat($this->getUrlsForItem($type, $item));

                    continue;
                }

                foreach ($definition['cmsPages'] as $cmsPage => $itemName) {
                    $item = (object)[
                        'type'      => $type,
                        'reference' => $reference,
                        'cmsPage'   => $cmsPage,
                        'nesting'   => data_get($definition, 'nesting', false),
                    ];
                    $urls = $urls->concat($this->getUrlsForItem($type, $itemName));
                }
            }

            return $urls;
        });
    }

    /**
     * Get all URLs for a single menu item.
     *
     * @param $type
     * @param $item
     *
     * @return array
     */
    public function getUrlsForItem($type, $item): array
    {
        $urls      = [];
        $apiResult = Event::fire('pages.menuitem.resolveItem', [$type, $item, $this->baseUrl, $this->theme]);

        if ( ! \is_array($apiResult)) {
            return [];
        }

        foreach ($apiResult as $itemInfo) {
            if (isset($itemInfo['url'])) {
                $urls[] = $itemInfo['url'];
            }

            if (isset($itemInfo['items'])) {
                $itemIterator = function ($items) use (&$itemIterator, &$urls) {
                    foreach ($items as $item) {
                        if (isset($item['url'])) {
                            $urls[] = $item['url'];
                        }

                        if (isset($item['items'])) {
                            $itemIterator($item['items']);
                        }
                    }
                };

                $itemIterator($itemInfo['items']);
            }
        }

        return $urls;
    }

    /**
     * Load all relevant Settings for the DomManipulator.
     *
     * @return DomManipulatorSettings
     */
    protected function getSettings(): DomManipulatorSettings
    {
        return DomManipulatorSettings::fromSettingsModel(Settings::instance());
    }
}