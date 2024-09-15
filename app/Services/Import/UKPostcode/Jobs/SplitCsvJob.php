<?php

namespace App\Services\Import\UKPostcode\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SplitCsvJob implements ShouldQueue
{
    protected $csvFilePath;
    protected $chunkDirPath;
    protected $chunkSize;

    public function __construct($csvFilePath, $chunkDirPath, $chunkSize = 1000)
    {
        $this->csvFilePath = $csvFilePath;
        $this->chunkDirPath = $chunkDirPath;
        $this->chunkSize = $chunkSize;
    }

    public function handle(): void
    {
        if (!Storage::exists($this->csvFilePath)) {
            Log::error('CSV file does not exist.');
            return;
        }

        // Clean and prepare storage directory
        Storage::deleteDirectory($this->chunkDirPath);
        Storage::makeDirectory($this->chunkDirPath);

        // Process CSV and split into chunks
        $this->splitCSVIntoChunks();
    }

    private function splitCSVIntoChunks()
    {
        $fullPath = Storage::path($this->csvFilePath);
        $handle = fopen($fullPath, 'r');
        $header = fgetcsv($handle); // Read the header row
        $rows = [];
        $chunkCount = 0;

        while (($data = fgetcsv($handle)) !== false) {
            $rows[] = $data;

            if (count($rows) === $this->chunkSize) {
                $this->storeChunk($rows, $chunkCount, $header);
                $rows = [];
                $chunkCount++;
            }
        }

        if (!empty($rows)) {
            $this->storeChunk($rows, $chunkCount, $header);
        }
    }

    private function storeChunk(array $rows, int $chunkCount, array $header)
    {
        $chunkFile = "{$this->chunkDirPath}/chunk_{$chunkCount}.csv";
        $handle = fopen(Storage::path($chunkFile), 'w');
        fputcsv($handle, $header); // Write the header to each chunk
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
        Log::info("Stored chunk {$chunkCount} at {$chunkFile}");
    }
}
