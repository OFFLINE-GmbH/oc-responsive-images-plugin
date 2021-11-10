<?php

namespace OFFLINE\ResponsiveImages\Console;

use Illuminate\Console\Command;
use OFFLINE\ResponsiveImages\Classes\Convert\FileHandler;
use Symfony\Component\Finder\Finder;

class WebpConvertCommand extends Command
{
    protected $signature = 'responsive-images:convert
                            {--format=webp : The format to convert the images}
                            {--include=* : The path(s) in which the images being contained}
                            {--include-since= : Strtotime-compatible string to render only the files created after a specific time}
                            {--size-limit=500 : Ignore files bigger than this value (in kB)}
                            {--exec-time-limit=60 : Define the timeout of the image-rendering}
                            {--compressor-path=cwebp : The path to the cwebp compressor}
                            {--compressor-args="" : Additional arguments for the compressor}';

    protected $description = 'Converts internal images into the given format';

    public function handle()
    {
        session(['process-directories' => 0]);
        session(['success-files' => 0]);
        session(['error-files' => 0]);

        foreach ($this->option('include') as $path) {
            session()->increment('process-directories');
            $this->info('starting convertion of ' . $path);

            $fileHandler = new FileHandler(
                $path,
                $this->option('format'),
                $this->option('size-limit'),
                $this->option('include-since'),
                $this->option('exec-time-limit'),
                $this->option('compressor-path'),
                $this->option('compressor-args')
            );

            // Dirs
            $finder = new Finder();
            $finder->directories()->in($path);

            $this->info('process parent-directory:' . $path);
            $fileHandler->process($path);
            foreach ($finder as $dir) {
                session()->increment('process-directories');
                $this->info('process directory:' . $dir->getPathname());
                $fileHandler->process($dir->getPathname());
            }

            $this->info(
                'processed ' .
                session()->pull('success-files') .
                ' files out of ' .
                session()->pull('process-directories') .
                ' directories'
            );

            if ($count = session()->pull('error-files') > 0) {
                $this->error('detected ' . $count . ' errors; see offline_responsiveimages_inconvertables');
            }
        }
    }
}