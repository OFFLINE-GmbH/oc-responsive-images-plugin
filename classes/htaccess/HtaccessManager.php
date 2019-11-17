<?php

namespace OFFLINE\ResponsiveImages\Classes\Htaccess;


class HtaccessManager
{
    /** @var HtaccessWriter */
    protected $manager;

    public function __construct(HtaccessWriter $manager = null)
    {
        $this->manager = $manager ?: new HtaccessWriter();
    }

    public function toggleSection($section, $status)
    {
        if ($status === true) {
            $this->manager->writeSection($section);
        } else {
            $this->manager->removeSection($section);
        }
    }

    public function save()
    {
        return $this->manager->writeContents();
    }
}