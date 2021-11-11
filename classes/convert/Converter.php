<?php

namespace OFFLINE\ResponsiveImages\Classes\Convert;

use Symfony\Component\Finder\SplFileInfo;

interface Converter
{
    function convert(SplFileInfo $file);
}
