<?php

namespace OFFLINE\ResponsiveImages\Classes\Convert;

class ConvertResult
{
    private $directories;
    private $files;
    private $errors = [];

    public function incrementDirectories(): void
    {
        $this->directories++;
    }

    public function incrementFiles(): void
    {
        $this->files++;
    }

    public function addError($error): void
    {
        $this->errors[] = $error;
    }

    public function print(): array
    {
        $result = "\n<fg=black;bg=green>Processing successful!</>\n";
        if ($this->errors) {
            $result = "\n<fg=black;bg=red>Processing failed!</>\n";
        }

        $directories = sprintf("\nDirectories:      %5d</>\n", $this->directories);
        $files = sprintf("Converted files:  %5d</>\n", $this->files);

        $errors = '';
        if ($this->errors) {
            foreach ($this->errors as $error) {
                $errors .= sprintf("\n<fg=white;bg=red>- %s</>", $error);
            }
        }

        return [$result, $directories, $files, $errors ?: ''];
    }
}
