<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\GenericLog;
use App\Models\FailedRecord;
use App\Models\UKPostCode;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ProcessDownloadedZipCSVFile implements ShouldQueue
{
    use Queueable;

    protected $dirPath;
    protected $chunkSize = 1000;
    protected $csvFilePath = '';
    protected $validationRules = [
        'postcode' => 'required|string|regex:/^([A-Z]{1,2}\d[A-Z\d]? \d[A-Z]{2})$/i',
        'latitude' => 'required|numeric|between:-90,90',
        'longitude' => 'required|numeric|between:-180,180',
    ];

    public function __construct()
    {
        $this->dirPath = 'ukPostcodeZip/Data';
    }

    public function handle(): void
    {
        try {
            if (!Storage::exists($this->dirPath)) {
                throw new FileNotFoundException("The directory does not exist: " . $this->dirPath);
            }

            $files = Storage::files($this->dirPath);
            $csvFiles = preg_grep('/\.csv$/i', $files);
            sort($csvFiles);

            $firstCsvFile = !empty($csvFiles) ? $csvFiles[0] : null;

            if ($firstCsvFile) {
                $fullPath = Storage::path($firstCsvFile);
                Log::info("Processing CSV file: " . $fullPath);
                $this->csvFilePath = $fullPath;
                $this->processCSVFile();
            } else {
                Log::info("No CSV files found in the directory.");
                throw new FileNotFoundException("The file does not exist: " . $firstCsvFile);
            }
        } catch (FileNotFoundException $e) {
            Log::error("Error: " . $e->getMessage());
            throw new \Exception($e->getMessage());
        } catch (Exception $e) {
            Log::error("Unexpected error: " . $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    protected function processCSVFile(): void
    {
        $timestamp = Carbon::now();
        $lastProcessedIndex = $this->getLastProcessedIndex();
        if (!empty($lastProcessedIndex)) {
            $this->removePostCodeFromLastProcessed();
        }
        $file = fopen($this->csvFilePath, 'r');
        $headers = fgetcsv($file);

        $postcodeIndex = array_search('pcds', $headers);
        $latitudeIndex = array_search('lat', $headers);
        $longitudeIndex = array_search('long', $headers);

        if ($postcodeIndex === false || $latitudeIndex === false || $longitudeIndex === false) {
            fclose($file);
            Log::error('Invalid CSV format: Required columns are missing');
            throw new \Exception("Invalid CSV format: Required columns are missing");
        }

        $index = 0;
        $rows = [];
        while (($data = fgetcsv($file)) !== false) {
            if ($index < $lastProcessedIndex) {
                $index++;
                continue;
            }

            $postcode = trim($data[$postcodeIndex]);

            // Only add new rows to the batch if the postcode does not exist (unique constraint will handle it)
            $rows[] = [
                'postcode' => $postcode,
                'latitude' => $data[$latitudeIndex],
                'longitude' => $data[$longitudeIndex],
                'lineNumber' => $index,
                'created_at' => $timestamp,
            ];

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

    protected function getLastProcessedIndex()
    {
        $log = GenericLog::where('log_type', 'csv_process_lnum')->first();
        return $log ? (int)$log->message : 0;
    }

    protected function updateLastProcessedIndex(int $index)
    {
        GenericLog::updateOrCreate(
            ['log_type' => 'csv_process_lnum'],
            ['message' => $index]
        );
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

    protected function processRows(array $rows)
    {
        try {
            DB::beginTransaction();
            $postcodes = array_column($rows, 'postcode');
            $existingPostcodes = UkPostcode::whereIn('postcode', $postcodes)
                ->pluck('postcode')
                ->toArray();
            $existingPostcodesSet = array_flip($existingPostcodes);
            $errors = [];
            $newRows = [];

            foreach ($rows as $row) {
                $validator = Validator::make($row, $this->validationRules);
                if ($validator->fails()) {
                    $row['error_message'] = implode(', ', $validator->errors()->all());
                    $errors[] = $row;
                } else if (isset($existingPostcodesSet[$row['postcode']])) {
                    $row['error_message'] = 'Postcode already exist';
                    $errors[] = $row;
                } else {
                    $newRows[] = $row;
                }
            }

            if (!empty($newRows)) {
                UkPostcode::insert(array_map(function ($row) {
                    return [
                        'postcode' => $row['postcode'],
                        'latitude' => $row['latitude'],
                        'longitude' => $row['longitude'],
                        'created_at' => $row['created_at'],
                    ];
                }, $newRows));
            }
            if (!empty($errors)) {
                FailedRecord::insert(array_map(function ($row) {
                    return [
                        'postcode' => $row['postcode'],
                        'latitude' => $row['latitude'],
                        'longitude' => $row['longitude'],
                        'created_at' => $row['created_at'],
                        'line_number' => $row['lineNumber'],
                        'error_message' => $row['error_message']
                    ];
                }, $errors));
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $failedRows = array_map(function ($row) use ($e) {
                return [
                    'postcode' => $row['postcode'],
                    'latitude' => $row['latitude'],
                    'longitude' => $row['longitude'],
                    'created_at' => $row['created_at'],
                    'error_message' => $e->getMessage(),
                    'line_number' => $row['lineNumber']
                ];
            }, $rows);
            FailedRecord::create($failedRows);
            Log::error('An error occurred: ' . $e->getMessage());
        }
        $lastPrimaryKey = UkPostcode::latest('id')->value('id');
        $this->updateLastProcessedPostCodeId($lastPrimaryKey);
    }
}
