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

    public function toggleSection($section, $status, $data = [])
    {
        if ($status === true) {
            $this->manager->writeSection($section, $data);
        } else {
            $this->manager->removeSection($section, $data);
        }
    }

    public function save()
    {
        return $this->manager->writeContents();
    }
}
