<?php

namespace OFFLINE\ResponsiveImages\Console;


use Illuminate\Console\Command;
use OFFLINE\ResponsiveImages\Classes\ImagePreloader;
use Symfony\Component\Console\Input\InputArgument;

class GenerateResizedImages extends Command
{

    protected $name = 'responsiveimages:generate';

    protected $description = 'Trys to generate all needed resized images';

    protected function getArguments()
    {
        return [
            ['theme', InputArgument::REQUIRED, 'The Theme to use.'],
        ];
    }

    public function handle()
    {

        $preloader = new ImagePreloader($this->argument('theme'));
        $preloader->preloadImages();

    }

}