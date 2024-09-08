<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessDownloadedZipCSVFile;
use App\Jobs\ProcessCSVChunk;

class SplitCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $dirPath;
    protected $chunkSize;
    protected $csvFilePath;

    public function __construct($csvFilePath, $chunkDirPath, $chunkSize = 1000)
    {
        $this->dirPath = $chunkDirPath;
        $this->chunkSize = $chunkSize;
        $this->csvFilePath = $csvFilePath;
    }

    public function handle(): void
    {
        if (!Storage::exists($this->csvFilePath)) {
            return;
        }

        if (Storage::exists($this->dirPath)) {
            Log::info('Storage folder cleaned');
            Storage::delete(Storage::files($this->dirPath));
        } else {
            Log::info('Storage folder created');
            Storage::makeDirectory($this->dirPath);
        }

        $fullPath = Storage::path($this->csvFilePath);

        $handle = fopen($fullPath, 'r');
        $header = fgetcsv($handle); // Get the header row
        $rows = [];
        $chunkCount = 0;

        while (($data = fgetcsv($handle)) !== false) {
            $rows[] = $data;

            // Once we have enough rows, create a chunk file
            if (count($rows) === $this->chunkSize) {
                $chunkFile = $this->dirPath . '/chunk_' . $chunkCount . '.csv';
                $this->createChunkFile($chunkFile, $header, $rows);
                ProcessCSVChunk::dispatch($chunkFile);
                $chunkCount++;
                if($chunkCount >= 20){
                    exit;
                }
                $rows = [];
            }
        }

        // Handle any remaining rows
        if (!empty($rows)) {
            $chunkFile = $this->dirPath . '/chunk_' . $chunkCount . '.csv';
            $this->createChunkFile($chunkFile, $header, $rows);
            ProcessCSVChunk::dispatch($chunkFile);
        }

        fclose($handle);
    }

    protected function createChunkFile($filePath, $header, $rows)
    {
        $fullPath = Storage::path($filePath);
        $handle = fopen($fullPath, 'w');
        fputcsv($handle, $header);
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
    }
}
