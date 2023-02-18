<?php namespace OFFLINE\ResponsiveImages\Console;

use Illuminate\Console\Command;

use Artisan;
use File;

/**
 * Clear Command
 */
class Clear extends Command
{
    /**
     * @var string name is the console command name
     */
    protected $name = 'responsive-images:clear';

    /**
     * @var string description is the console command description
     */
    protected $description = 'No description provided yet...';

    /**
     * handle executes the console command
     */
    public function handle()
    {
        // Clear cache and thumbnails
        Artisan::call('cache:clear');
        Artisan::call('october:util', [
            'name' => 'purge thumbs',
            '--force' => true,
        ]);

        // Clear resized images
        $path = storage_path('app/resources/resize');
        File::cleanDirectory($path);
        
        // Clear responsive images
        $path = storage_path('temp/public');
        $root = new \RecursiveDirectoryIterator($path);
        $directories = new \RecursiveIteratorIterator($root);
        foreach ($directories as $directory) {
            File::delete(File::glob($directory->getPath().'/thumb_*'));
        }
    }

    /**
     * getArguments get the console command arguments
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * getOptions get the console command options
     */
    protected function getOptions()
    {
        return [];
    }
}
