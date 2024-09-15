<?php

namespace App\Services\Import\UKPostcode\Context;
use App\Services\Import\UKPostCode\Factories\UKPostcodeImportStrategyFactory;
use App\Services\Import\UKPostCode\Strategies\PostcodeImportStrategy;

// responsible for choosing the appropriate strategy.
class UKPostcodeImportContext
{
    private $strategy;

    public function __construct(UKPostcodeImportStrategyFactory $strategyFactory)
    {
        $this->strategyFactory = $strategyFactory;
    }

    public function executeImport(string $strategy)
    {

        // Select the appropriate strategy using the factory
        $strategyInstance = $this->strategyFactory->create($strategy);

        // Execute the import process using the selected strategy
        $strategyInstance->import();
    }
}
