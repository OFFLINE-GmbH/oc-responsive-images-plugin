<?php

namespace OFFLINE\ResponsiveImages\Classes\Convert;

class ConvertResult
{
    private $directories;
    private $files;
    private $errors = [];

    public function incrementDirectory()
    {
        $this->directories++;
    }

    public function incrementFile()
    {
        $this->files++;
    }

    public function addError($error)
    {
        $this->errors[] = $error;
    }

    public function print() {
        $directories = sprintf("\n<fg=black;bg=green>Processed directories: %d</>\n", $this->directories);
        $files = sprintf("\n<fg=black;bg=green>Successfully processed files: %d</>\n", $this->files);

        $errors = "";
        $countedErrors = 0;
        if ($this->errors) {
            $countedErrors = sprintf("\n\n<fg=white;bg=red>Errors occurred: %d</>", count($this->errors));
            foreach ($this->errors as $error) {
                $errors = $errors . sprintf("\n<fg=white;bg=red>- %s</>", $error);
            }
        }


        return [$directories, $files, $countedErrors ?: '', $errors ?: ''];
    }
}
