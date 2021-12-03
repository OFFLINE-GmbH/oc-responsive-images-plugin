<?php

namespace OFFLINE\ResponsiveImages\Classes\Convert;

use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Finder\Finder;

class PathProcessor
{
    /**
     * @var string
     */
    private $path;
    /**
     * @var Converter
     */
    private $converter;
    /**
     * @var PathProcessorOptions
     */
    private $options;
    /**
     * @var ConvertResult
     */
    private $result;

    public function __construct(
        string $path,
        Converter $converter,
        PathProcessorOptions $options,
        ConvertResult $result
    ) {
        $this->path = $path;
        $this->converter = $converter;
        $this->options = $options;
        $this->result = $result;
    }

    public function setOutput(OutputStyle $output)
    {
        $this->output = $output;
    }

    /**
     * Read directory and filter the files to convert
     *
     * @param string $dir
     */
    public function process(string $dir)
    {
        $finder = $this->filterFiles(
            $this->filesInDir($dir)
        );

        if ($finder->count() === 0) {
            return;
        }

        foreach ($finder->files() as $file) {
            try {
                $this->converter->convert($file);
                $this->output->write(sprintf("<info>-> converting %-s</info>\n", $file->getFilename()));
                $this->result->incrementFiles();
            } catch (\Throwable $e) {
                DB::table('offline_responsiveimages_inconvertibles')->insert([
                    'filename' => $file->getFilename(),
                    'path' => $file->getPath(),
                    'error' => $e->getMessage()
                ]);
                $this->result->addError($e->getMessage());
            }
        }
        $this->output->newLine();
    }

    /**
     * @param string $dir
     * @return Finder
     */
    public function filesInDir(string $dir): Finder
    {
        $finder = Finder::create()->depth(0)->files()->in($dir);

        if ($this->options->getSize()) {
            $finder->size('<= ' . $this->options->getSize() . 'K');
        }
        if ($this->options->getSince()) {
            $finder->date('>= ' . $this->options->getSince());
        }

        $finder->name('*.webp');

        if ($this->options->getAllowedExtensions()) {
            foreach ($this->options->getAllowedExtensions() as $extension) {
                $finder->name('*.' . $extension);
            }
        }
        return $finder;
    }

    /**
     * Remove files that should not be converted.
     *
     * @param Finder $finder
     * @return Finder
     */
    public function filterFiles(Finder $finder): Finder
    {
        return $this->filterInconvertableFiles(
            $this->filterConvertedFiles($finder)
        );
    }

    /**
     * Remove files that are already converted.
     *
     * @param Finder $finder
     * @return Finder
     */
    public function filterConvertedFiles(Finder $finder): Finder
    {
        foreach ($finder as $file) {
            if (str_contains($file->getFilename(), $this->options->getFormat())) {
                $excludeFile = str_replace('.' . $this->options->getFormat(), '', $file->getFilename());
                $finder->notName($excludeFile)->notName($file->getFilename());
            }
        }
        return $finder;
    }

    /**
     * Remove files that could not be converted.
     *
     * @param Finder $finder
     * @return Finder
     */
    public function filterInconvertableFiles(Finder $finder): Finder
    {
        $inconvertibles = DB::table('offline_responsiveimages_inconvertibles')
            ->where('path', $this->path)
            ->select(['filename'])
            ->get()
            ->pluck(null, 'filename');

        return $finder->filter(function (\SplFileInfo $file) use ($inconvertibles) {
            return !$inconvertibles->has($file->getFilename());
        });
    }
}
