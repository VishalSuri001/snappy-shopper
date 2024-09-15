<?php

namespace App\Services\Import\UKPostcode\Jobs;

use App\Services\Import\UKPostcode\Jobs\Utilities\CSVRowValidator;
use App\Services\Import\UKPostcode\Jobs\Utilities\CSVFileProcessor;
use App\Models\FailedRecord;
use App\Models\GenericLog;
use App\Models\UKPostCode;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class ProcessDownloadedZipCSVFile implements ShouldQueue
{
    use Queueable;

    protected $dirPath;
    protected $chunkSize = 1000;
    protected $csvFileProcessor;
    protected $csvRowValidator;
    protected $csvFilePath;
    protected $validationRules = [
        'postcode' => 'required|string|regex:/^([A-Z]{1,2}\d[A-Z\d]? \d[A-Z]{2})$/i',
        'latitude' => 'required|numeric|between:-90,90',
        'longitude' => 'required|numeric|between:-180,180',
    ];

    public function __construct()
    {
        // Load paths from config
        $this->dirPath = Config::get('postcode.csv_dir_path');
        $this->csvFilePath = Config::get('postcode.csv_file_path');
        $this->csvRowValidator = new CSVRowValidator();
        $this->csvFileProcessor = new CSVFileProcessor($this->dirPath);
    }

    public function handle(): void
    {
        try {
            $csvFilePath = $this->csvFileProcessor->getFirstCSVFilePath();
            Log::info("Processing CSV file: " . $csvFilePath);

            $this->processCSVFile($csvFilePath);
        } catch (\Exception $e) {
            Log::error("Error: " . $e->getMessage());
            throw $e; // Optionally re-throw to handle retry attempts
        }
    }

    protected function processCSVFile(string $csvFilePath): void
    {
        $lastProcessedIndex = $this->getLastProcessedIndex();
        $file = fopen($csvFilePath, 'r');
        $headers = fgetcsv($file);

        $postcodeIndex = array_search('pcds', $headers);
        $latitudeIndex = array_search('lat', $headers);
        $longitudeIndex = array_search('long', $headers);

        if ($postcodeIndex === false || $latitudeIndex === false || $longitudeIndex === false) {
            fclose($file);
            Log::error('Invalid CSV format: Required columns are missing');
            throw new \Exception("Invalid CSV format");
        }

        $index = 0;
        $rows = [];

        while (($data = fgetcsv($file)) !== false) {
            if ($index < $lastProcessedIndex) {
                $index++;
                continue;
            }

            $row = $this->prepareRow($data, $postcodeIndex, $latitudeIndex, $longitudeIndex);
            $rows[] = $row;

            if (count($rows) >= $this->chunkSize) {
                $this->processRows($rows);
                $this->updateLastProcessedIndex($index);
                $rows = [];
            }

            $index++;
        }

        if (count($rows) > 0) {
            $this->processRows($rows);
        }

        fclose($file);
    }

    protected function prepareRow($data, $postcodeIndex, $latitudeIndex, $longitudeIndex)
    {
        return [
            'postcode' => trim($data[$postcodeIndex]),
            'latitude' => $data[$latitudeIndex],
            'longitude' => $data[$longitudeIndex],
        ];
    }

    protected function processRows(array $rows): void
    {
        DB::beginTransaction();
        try {
            $validatedRows = [];
            $errors = [];

            foreach ($rows as $row) {
                $validationErrors = $this->csvRowValidator->validate($row);

                if (!empty($validationErrors)) {
                    $errors[] = array_merge($row, ['error_message' => implode(', ', $validationErrors)]);
                } else {
                    $validatedRows[] = $row;
                }
            }

            // Insert valid rows
            if (!empty($validatedRows)) {
                // Perform database insertion here
            }

            // Log or store failed records
            if (!empty($errors)) {
                // Insert failed records into FailedRecord table
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction error: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function updateLastProcessedIndex(int $index)
    {
        GenericLog::updateOrCreate(
            ['log_type' => 'csv_process_lnum'],
            ['message' => $index]
        );
    }

    protected function getLastProcessedIndex()
    {
        $log = GenericLog::where('log_type', 'csv_process_lnum')->first();
        return $log ? (int)$log->message : 0;
    }

    protected function removePostCodeFromLastProcessed()
    {
        $log = GenericLog::where('log_type', 'csv_process_postcode_id')->first();
        $postcodePK = $log ? (int)$log->message : 0;
        if(!empty($postcodePK)){
            UkPostcode::where('id', '>', $postcodePK)->delete();
        }
    }

    protected function updateLastProcessedPostCodeId(int $index)
    {
        GenericLog::updateOrCreate(
            ['log_type' => 'csv_process_postcode_id'],
            ['message' => $index]
        );
    }
}
