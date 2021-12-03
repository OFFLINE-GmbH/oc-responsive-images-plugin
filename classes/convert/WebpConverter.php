<?php

namespace OFFLINE\ResponsiveImages\Classes\Convert;

use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class WebpConverter implements Converter
{
    private $path;
    private $args;

    public function __construct($path, $args)
    {
        $this->path = $path;
        $this->args = $args;
    }

    public function convert(SplFileInfo $file)
    {
        $process = new Process([
            $this->path,
            $this->args,
            $file->getRealPath(),
            '-o',
            $file->getRealPath() . '.webp'
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
