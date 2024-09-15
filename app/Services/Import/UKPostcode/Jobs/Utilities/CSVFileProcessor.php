<?php

namespace App\Services\Import\UKPostcode\Jobs\Utilities;

use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class CSVFileProcessor
{
    protected $dirPath;

    public function __construct($dirPath)
    {
        $this->dirPath = $dirPath;
    }

    public function getFirstCSVFilePath(): string
    {
        if (!Storage::exists($this->dirPath)) {
            throw new FileNotFoundException("The directory does not exist: " . $this->dirPath);
        }

        $files = Storage::files($this->dirPath);
        $csvFiles = preg_grep('/\.csv$/i', $files);

        if (empty($csvFiles)) {
            throw new FileNotFoundException("No CSV files found in the directory.");
        }

        sort($csvFiles); // Sort CSV files and get the first one

        return Storage::path($csvFiles[0]);
    }
}
