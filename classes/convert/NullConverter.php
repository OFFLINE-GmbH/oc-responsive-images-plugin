<?php

namespace OFFLINE\ResponsiveImages\Classes\Convert;

class NullConverter implements Converter
{

    public function __construct()
    {
    }

    public function convert($files)
    {
        throw new \ApplicationException('Format to convert the files is not supported. See https://github.com/OFFLINE-GmbH/oc-responsive-images-plugin for further information.');
    }

    function getRange(int $max)
    {
    }
}