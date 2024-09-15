<?php

namespace App\Services\Import\UKPostCode\Strategies;

use App\Services\Import\UKPostcode\Jobs\DownloadAndUnZipPostcodeZip;
use App\Services\Import\UKPostcode\Jobs\SplitCsvJob;
use Illuminate\Support\Facades\Log;

class SplitCSVImportStrategy implements PostcodeImportStrategy
{
    protected $zipUrl;
    protected $postcodeCSVPath;
    protected $chunkDirPath;

    public function __construct()
    {
        // Fetching values from the config file
        $this->zipUrl = Config::get('postcode.download_url');
        $this->postcodeCSVPath = Config::get('postcode.postcode_csv_path');
        $this->chunkDirPath = Config::get('postcode.chunk_dir_path');
    }

    public function import()
    {
        try {
            Log::info('Starting postcode import with CSV splitting.');
            DownloadAndUnZipPostcodeZip::dispatchSync($this->zipUrl);
            SplitCsvJob::dispatchSync($this->postcodeCSVPath, $this->chunkDirPath);
            Log::info('UK postcode import with splitting completed.');
        } catch (\Exception $e) {
            Log::error('Error during postcode import with CSV splitting: ' . $e->getMessage());
            throw $e;
        }
    }
}
