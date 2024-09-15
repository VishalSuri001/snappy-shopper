<?php

namespace App\Services\Import\UKPostcode\Jobs;

use App\Jobs\Exception;
use App\Models\FailedRecord;
use App\Models\UKPostCode;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProcessCSVChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    protected $filePath;
    protected $errorObserved = false;
    protected $validationRules = [
        'postcode' => 'required|string|regex:/^([A-Z]{1,2}\d[A-Z\d]? \d[A-Z]{2})$/i',
        'latitude' => 'required|numeric|between:-90,90',
        'longitude' => 'required|numeric|between:-180,180',
    ];

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function handle(): void
    {
        try {

            if (!Storage::exists($this->filePath)) {
                throw new \Exception("The file does not exist: " . $this->filePath);
            }
            $filePath = Storage::path($this->filePath);
            $timestamp = Carbon::now();
            $file = fopen($filePath, 'r');
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
                $postcode = trim($data[$postcodeIndex]);
                $rows[] = [
                    'postcode' => $postcode,
                    'fileName' => basename($this->filePath),
                    'latitude' => $data[$latitudeIndex],
                    'longitude' => $data[$longitudeIndex],
                    'lineNumber' => $index,
                    'created_at' => $timestamp,
                ];
            }
            $this->processRows($rows);
            fclose($file);
            if (!$this->errorObserved) {
                Storage::delete($this->filePath);
            }
        } catch (Exception $e) {
            Log::error("Unexpected error: " . $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    protected function processRows($rows)
    {
        $errors = [];
        $newRows = [];
        try {
            DB::beginTransaction();
            $postcodes = array_column($rows, 'postcode');
            $existingPostcodes = UkPostcode::whereIn('postcode', $postcodes)
                ->pluck('postcode')
                ->toArray();
            $existingPostcodesSet = array_flip($existingPostcodes);

            foreach ($rows as $row) {
                $validator = Validator::make($row, $this->validationRules);
                if ($validator->fails()) {
                    $errorLog = $validator->errors()->all();
                    Log::error('Validation failed', $errorLog);
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
                $failedRows = array_map(function ($row) {
                    $failedRow = [
                        'postcode' => $row['postcode'],
                        'latitude' => $row['latitude'],
                        'longitude' => $row['longitude'],
                        'file_name' => $row['fileName'],
                        'created_at' => $row['created_at'],
                        'line_number' => $row['lineNumber'],
                        'error_message' => $row['error_message']
                    ];
                    return $failedRow;
                }, $errors);
                FailedRecord::insert($failedRows);
            }
            $this->errorObserved = count($errors);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction rollback - ', $e->getMessage());
            $this->errorObserved = count($failedRows);
        }
    }
}
