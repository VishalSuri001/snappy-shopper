<?php

namespace App\Services\DB;
use App\Models\UKPostCode;
use App\Models\FailedRecord;

class DataCleanupService
{
    public function clearOldData()
    {
        UKPostCode::truncate();
        FailedRecord::truncate();
    }
}
