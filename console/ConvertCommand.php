<?php

namespace OFFLINE\ResponsiveImages\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use OFFLINE\ResponsiveImages\Classes\Convert\Converter;
use OFFLINE\ResponsiveImages\Classes\Convert\ConvertResult;
use OFFLINE\ResponsiveImages\Classes\Convert\PathProcessor;
use OFFLINE\ResponsiveImages\Classes\Convert\PathProcessorOptions;
use OFFLINE\ResponsiveImages\Classes\Convert\WebpConverter;
use OFFLINE\ResponsiveImages\Models\Settings;
use Symfony\Component\Finder\Finder;

class ConvertCommand extends Command
{
    protected $signature = 'responsive-images:convert
                            {--format=webp : The format to convert the images}
                            {--include=* : The path(s) in which the images being contained}
                            {--include-since= : Strtotime-compatible string to render only the files created after a specific time}
                            {--size-limit=1500 : Ignore files bigger than this value (in kB)}
                            {--exec-time-limit=60 : Define the timeout of the image-rendering}
                            {--converter-path=cwebp : The path to the cwebp converter}
                            {--converter-args= : Additional arguments for the converter}';

    protected $description = 'Converts images into a given format';

    /**
     * @throws \ApplicationException
     */
    public function handle()
    {
        set_time_limit((int)$this->option('exec-time-limit'));

        $result = new ConvertResult();
        $converter = $this->getConverter($result);
        $options = new PathProcessorOptions(
            $this->option('format'),
            $this->option('size-limit'),
            $this->option('include-since')
        );

        $this->output->section('Converting files to webp');

        foreach ($this->option('include') as $path) {
            try {
                $processor = new PathProcessor(
                    $path,
                    $converter,
                    $options,
                    $result
                );
                $processor->setOutput($this->output);

                $directories = Finder::create()->directories()->in($path)->append([$path]);
                foreach ($directories as $directory) {
                    $result->incrementDirectories();
                    $this->line($directory->getPathname());

                    $processor->process($directory->getPathname());
                }

            } catch (\Throwable $e) {
                logger()->error(sprintf('[OFFLINE.ResponsiveImages]: webp-conversion failed: %s', $e));
                $this->output->write(sprintf("\n<fg=white;bg=red>Conversion of '%s' failed!</>", $path), true);
                $this->output->write("\n<fg=white;bg=red>" . $e->getMessage() . "</>\n", true);
                $this->output->write("<fg=blue>" . $e->getTraceAsString() . "</>\n", true);
                $result->addError(sprintf("Conversion of '%s' failed!", $path));
            }
        }

        $this->output->write($result->print());

        Settings::set('latest_conversion', Carbon::now());
    }


    private function getConverter($result): Converter
    {
        switch ($this->option('format')) {
            case 'webp':
                return new WebpConverter(
                    $this->option('converter-path'),
                    $this->option('converter-args'),
                    $result
                );
            default:
                throw new \ApplicationException('Format "' . $this->format . '" to convert the files is not supported. See https://github.com/OFFLINE-GmbH/oc-responsive-images-plugin for further information.');

        }
    }
}
