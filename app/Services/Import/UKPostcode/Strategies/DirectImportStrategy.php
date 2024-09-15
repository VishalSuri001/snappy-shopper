<?php
namespace App\Services\Import\UKPostCode\Strategies;

use App\Services\Import\UKPostcode\Jobs\DownloadAndUnZipPostcodeZip;
use App\Services\Import\UKPostcode\Jobs\ProcessDownloadedZipCSVFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;


class DirectImportStrategy implements PostcodeImportStrategy
{
    protected $zipUrl;
    protected $csvDirPath;
    protected $chunkDirPath;
    protected $postcodeCSVPath;

    public function __construct()
    {
        // Fetching values from config
        $this->zipUrl = Config::get('postcode.download_url');
        $this->csvDirPath = Config::get('postcode.csv_dir_path');
        $this->chunkDirPath = Config::get('postcode.chunk_dir_path');
        $this->postcodeCSVPath = Config::get('postcode.postcode_csv_path');
    }

    public function import()
    {
        try {
            Log::info('Starting direct UK postcode import.');
            DownloadAndUnZipPostcodeZip::dispatchSync($this->zipUrl);
            ProcessDownloadedZipCSVFile::dispatchSync();
            Log::info('UK postcode import completed.');
        } catch (\Exception $e) {
            Log::error('Error during direct postcode import: ' . $e->getMessage());
            throw $e;
        }
    }
}
