<?php

namespace OFFLINE\ResponsiveImages\Classes\Convert;

use Illuminate\Support\Facades\DB;
use Symfony\Component\Finder\Finder;

class WebpConverter implements Converter
{
    private $path;
    private $execTimeLimit;
    private $args;

    public function __construct($path, $execTimeLimit, $args)
    {
        $this->path = $path;
        $this->execTimeLimit = $execTimeLimit;
        $this->args = $args;
    }

    public function convert($files)
    {
        foreach ($this->getRange(count($files)) as $range) {
            set_time_limit((int)$this->execTimeLimit);
            $error = exec(
                $this->path .
                ' ' . $this->args .
                ' ' . $files[$range]->getRealPath() .
                ' -o ' .
                $files[$range]->getRealPath() .
                '.webp'
            );

            if ($error) {
                DB::table('offline_responsiveimages_inconvertables')->insert([
                    'filename' => $files[$range]->getFilename(),
                    'path' => $files[$range]->getPath()
                ]);
                session()->increment('success-files');
            }
            session()->increment('success-files');
        }
    }

    function getRange($max): \Generator
    {
        for ($i = 0; $i < $max; $i++) {
            yield $i;
        }
    }
}