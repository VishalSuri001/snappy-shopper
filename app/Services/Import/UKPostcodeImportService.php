<?php
namespace App\Services\Import;

use App\Services\DB\DataCleanupService;
use App\Services\Import\UKPostcode\Jobs\DownloadAndUnZipPostcodeZip;
use App\Services\Import\UKPostcode\Jobs\ProcessDownloadedZipCSVFile;
use App\Services\Import\UKPostcode\Jobs\SplitCsvJob;
use Illuminate\Support\Facades\Log;

class UKPostcodeImportService
{
    protected $zipUrl = 'https://parlvid.mysociety.org/os/ONSPD/2022-11.zip';
    protected $csvDirPath = 'ukPostcodeZip/Data';
    protected $chunkDirPath = 'ukpostcode/CSVChunks';
    protected $postcodeCSVPath = 'ukpostcode/postcodes.csv';

    public function __construct(DataCleanupService $dataCleanupService)
    {
        $this->dataCleanupService = $dataCleanupService;
    }

    public function importPostcodes()
    {
        try {
            Log::info('Starting the download and processing of UK postcodes.');
            $this->dataCleanupService->clearOldData();

            // Download and unzip file
            DownloadAndUnZipPostcodeZip::dispatchSync($this->zipUrl);

            // Process the downloaded CSV file
            ProcessDownloadedZipCSVFile::dispatchSync();
            Log::info('UK postcode import completed.');
        } catch (\Exception $e) {
            Log::error('Error during postcode import: ' . $e->getMessage());
            throw $e;
        }
    }

    public function importPostcodesWithSplitting()
    {
        try {
            Log::info('Starting the download and processing of UK postcodes with CSV splitting.');
            $this->dataCleanupService->clearOldData();

            // Download and unzip file
            DownloadAndUnZipPostcodeZip::dispatchSync($this->zipUrl);

            // Split the CSV into chunks and process them
            SplitCsvJob::dispatchSync($this->postcodeCSVPath, $this->chunkDirPath);
            Log::info('UK postcode import with splitting completed.');
        } catch (\Exception $e) {
            Log::error('Error during postcode import with splitting: ' . $e->getMessage());
            throw $e;
        }
    }
}
