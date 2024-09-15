<?php

namespace App\Services\Import\UKPostcode\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DownloadAndUnZipPostcodeZip implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function handle(): void
    {
        $folder = 'ukPostcodeZip';
        $fileName = 'postcode-archive.zip';
        $filePath = $folder . '/' . $fileName;
        $storagePath = 'app/'. $folder;
        $storageFilePath = 'app/'. $filePath;
        $csvFilePath = $storagePath . '/Data';
        $toProcessCSVDir = 'app/ukpostcode';

        if (Storage::exists($folder)) {
            Log::info('Storage folder cleaned');
            Storage::delete(Storage::files($folder));
        } else {
            Log::info('Storage folder created');
            Storage::makeDirectory($folder);
        }

        Log::info('Archive download started');
        $zipContent = Http::timeout(120)->get($this->url)->body();
        Log::info('Archive download completed');

        Storage::put($filePath, $zipContent);
        Log::info('File Stored');

        $zip = new \ZipArchive;
        if ($zip->open(storage_path($storageFilePath)) === TRUE) {
            $zip->extractTo(storage_path($storagePath));
            $zip->close();
        } else {
            Log::error('Failed to open the ZIP file');
            return;
        }

        $files = Storage::files($csvFilePath);
        $csvFiles = preg_grep('/\.csv$/i', $files);
        sort($csvFiles);

        $firstCsvFile = !empty($csvFiles) ? $csvFiles[0] : null;
        if ($firstCsvFile) {
            $fullPath = Storage::path($firstCsvFile);
            Storage::deleteDirectory($toProcessCSVDir);
            Storage::makeDirectory($toProcessCSVDir);
            Storage::copy($fullPath, $toProcessCSVDir . '/postcodes.csv');
        }
        Log::info('Postcode extracted complete.');
    }
}
