<?php

namespace App\Services\Import\UKPostcode\Jobs\Utilities;

use Illuminate\Support\Facades\Validator;

class CSVRowValidator
{
    protected $validationRules = [
        'postcode' => 'required|string|regex:/^([A-Z]{1,2}\d[A-Z\d]? \d[A-Z]{2})$/i',
        'latitude' => 'required|numeric|between:-90,90',
        'longitude' => 'required|numeric|between:-180,180',
    ];

    public function validate(array $row)
    {
        $validator = Validator::make($row, $this->validationRules);

        if ($validator->fails()) {
            return $validator->errors()->all();
        }

        return [];
    }
}
