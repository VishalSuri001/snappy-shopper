<?php

namespace App\Factories;

use App\Services\Import\UKPostcode\Jobs\ProcessCSVChunk;
use App\Services\Import\UKPostcode\Jobs\ProcessDownloadedZipCSVFile;

class CSVProcessorFactory
{
    public static function createProcessor($csvFilePath, $type = 'direct', $chunkSize = 1000)
    {
        switch ($type) {
            case 'split':
                return new ProcessCSVChunk($csvFilePath, $chunkSize);
            default:
                return new ProcessDownloadedZipCSVFile($csvFilePath);
        }
    }
}
