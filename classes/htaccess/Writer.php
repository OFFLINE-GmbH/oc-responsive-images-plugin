<?php

namespace OFFLINE\ResponsiveImages\Classes\Htaccess;

class Writer
{
    /**
     * @var string
     */
    public $path;
    /**
     * @var array
     */
    protected $lines;
    /**
     * @var string
     */
    protected $comment;
    /**
     * @var string
     */
    protected $rule;

    public function __construct()
    {
        $this->path  = base_path('.htaccess');
        $this->lines = file($this->path);

        $this->comment = '# OFFLINE.ResponsiveImages: Allow access to webp.php';
        $this->rule    = 'RewriteRule ^plugins/offline/responsiveimages/webp\.php - [L]';
    }

    public function write()
    {
        if (count($this->lines) < 1) {
            return;
        }

        return file_put_contents($this->path, implode('', $this->lines));
    }

    /**
     * Add the whitelist rule for the webp.php helper script.
     */
    public function addWhitelist()
    {
        // make sure the rule is only added once.
        $ruleAdded = false;
        $newLines  = [];

        foreach ($this->lines as $line) {
            $newLines[] = $line;
            if ($ruleAdded === false && trim($line) === 'RewriteEngine On') {
                $ruleAdded = true;

                $newLines[] = "\n";
                $newLines[] = sprintf("    %s\n", $this->comment);
                $newLines[] = sprintf("    %s\n", $this->rule);
            }
        }

        $this->lines = $newLines;
    }

    /**
     * Remove the whitelist rule for the webp.php helper script.
     */
    public function removeWhitelist()
    {
        $newLines = [];
        foreach ($this->lines as $line) {
            if (trim($line) === $this->comment) {
                continue;
            }
            if (trim($line) === $this->rule) {
                continue;
            }
            $newLines[] = $line;
        }
        $this->lines = $newLines;
    }
}