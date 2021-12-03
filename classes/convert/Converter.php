<?php

namespace OFFLINE\ResponsiveImages\Classes\Convert;

use Symfony\Component\Finder\SplFileInfo;

interface Converter
{
    public function convert(SplFileInfo $file);
}
