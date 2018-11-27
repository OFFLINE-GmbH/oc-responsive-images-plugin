<?php

namespace OFFLINE\ResponsiveImages\Console;


use Cms\Classes\Theme;
use Illuminate\Console\Command;
use OFFLINE\ResponsiveImages\Classes\ImagePreloader;
use Symfony\Component\Console\Input\InputArgument;

class GenerateResizedImages extends Command
{
    protected $name = 'responsive-images:generate';
    protected $description = 'Tries to resize all used images.';

    protected function getArguments()
    {
        return [
            ['theme', InputArgument::OPTIONAL, 'The theme to use.'],
        ];
    }

    public function handle()
    {
        $theme = $this->argument('theme');
        if ( ! $theme) {
            $theme = Theme::getActiveThemeCode();
        }

        (new ImagePreloader($theme, $this->getOutput()))->preload();
    }
}