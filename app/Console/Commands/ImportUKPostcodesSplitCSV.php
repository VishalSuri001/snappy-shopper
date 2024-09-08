<?php

namespace App\Console\Commands;

use App\Jobs\SplitCsvJob;
use App\Models\FailedRecord;
use App\Models\UKPostCode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Jobs\DownloadAndUnZipPostcodeZip;
use App\Jobs\ProcessDownloadedZipCSVFile;
use Mockery\Exception;

class ImportUKPostcodesSplitCSV extends Command
{
    protected $signature = 'app:download-import-uk-postcodes';
    protected $description = 'Download and Import UK postcodes';
    private $zipUrl = 'https://parlvid.mysociety.org/os/ONSPD/2022-11.zip';
    protected $csvDirPath = 'ukPostcodeZip/Data';
    protected $chunkDirPath = 'ukpostcode/CSVChunks';
    protected $postcodeCSVPath = 'ukpostcode/postcodes.csv';

    public function handle()
    {
        $bold = "\033[1m";
        $reset = "\033[0m";
        $this->info($bold. 'Download and Import UK postcodes');
        $this->info('Process initiated...'. $reset);

        try {
            UKPostCode::truncate();
            FailedRecord::truncate();

            // Download and unzip file
            $this->info('1-Download and unzip file is started');
            (new DownloadAndUnZipPostcodeZip($this->zipUrl))->handle();
            $this->info('1-Download and unzip file is completed');

            $this->info('2-CSV Process is started');
            (new SplitCsvJob($this->postcodeCSVPath, $this->chunkDirPath, 2000))->handle();;
            $this->info('2-CSV Process is completed');


        } catch (\Exception $e) {
            $this->info($bold.'Process failed with error: ' . $e->getMessage());
        }
        return 0;
    }
}
