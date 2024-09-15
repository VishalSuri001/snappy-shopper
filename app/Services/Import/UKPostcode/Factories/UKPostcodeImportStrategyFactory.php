<?php

namespace App\Services\Import\UKPostCode\Factories;

use App\Services\Import\UKPostCode\Strategies\DirectImportStrategy;
use App\Services\Import\UKPostCode\Strategies\SplitCSVImportStrategy;
use App\Services\Import\UKPostCode\Strategies\ProcessAndSeparateCSVStrategy;

class UKPostcodeImportStrategyFactory
{
    public function create(string $strategy)
    {
        switch ($strategy) {
            case 'csv':
                return app(ProcessAndSeparateCSVStrategy::class);
            case 'split':
                return app(SplitCSVImportStrategy::class);
            case 'db':
            default:
                return app(DirectImportStrategy::class);
        }
    }
}
