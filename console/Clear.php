<?php namespace OFFLINE\ResponsiveImages\Console;

use Illuminate\Console\Command;

use Artisan;
use File;

use OFFLINE\ResponsiveImages\Models\Settings;

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
    protected $description = 'Clear generated responsive images';

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
        $extensions = Settings::get('allowed_extensions');
        foreach ($directories as $directory) {
            File::delete(File::glob($directory->getPath().'/*.{'.$extensions.'}', GLOB_BRACE));
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
