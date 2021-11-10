<?php

namespace OFFLINE\ResponsiveImages\Classes\Convert;

use ApplicationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Finder\Finder;

class FileHandler
{
    /**
     * @var string
     */
    private $path;
    /**
     * @var string
     */
    private $format;
    /**
     * @var string
     */
    private $size;
    /**
     * @var string
     */
    private $since;
    /**
     * @var string
     */
    private $execTimeLimit;
    /**
     * @var string
     */
    private $compressorPath;
    /**
     * @var string
     */
    private $compressorArgs;

    public function __construct(
        string $path,
        string $format,
        string $size,
        string $since,
        string $execTimeLimit,
        string $compressorPath,
        string $compressorArgs
    ) {
        $this->path = $path;
        $this->format = $format;
        $this->size = $size;
        $this->since = $since;
        $this->execTimeLimit = $execTimeLimit;
        $this->compressorPath = $compressorPath;
        $this->compressorArgs = $compressorArgs;
    }

    /**
     * Read directory and filter the files to convert
     *
     * @param string $dir
     */
    public function process(string $dir)
    {
        // Files per dir
        $finder = new Finder();
        $finder->depth(0)->files()->in($dir);
        if ($this->size) {
            $finder->size('<= ' . $this->size . 'K');
        }
        if ($this->since) {
            $finder->date('>= ' . $this->since);
        }

        foreach ($finder as $file) {
            if (str_contains($file->getFilename(), $this->format)) {
                $excludeFile = str_replace('.' . $this->format, '', $file->getFilename());
                $finder->notName($excludeFile)->notName($file->getFilename());
            }
        }

        $files = [];
        foreach ($finder as $file) {
            if (
                DB::table('offline_responsiveimages_inconvertables')
                    ->where('filename', $file->getFilename())
                    ->where('path')
                    ->exists()
            ) {
                info('ignoring ' . $file->getRealPath());
                continue;
            }
            $files[] = $file;
        }

        if (count($files) == 0) {
            info('no files to be converted in ' . $this->path);
            return;
        }

        $this->convert($files);
    }

    /**
     * @throws ApplicationException
     */
    private function convert($files)
    {
        switch ($this->format) {
            case 'webp':
                $converter = new WebpConverter(
                    $this->compressorPath,
                    $this->execTimeLimit,
                    $this->compressorArgs
                );
                break;
            default:
                $converter = new NullConverter();
        }

        $converter->convert($files);
    }


}