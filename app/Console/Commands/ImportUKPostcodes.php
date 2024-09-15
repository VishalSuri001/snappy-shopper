<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Import\UKPostCode\Context\UKPostcodeImportContext;

class ImportUKPostcodes extends Command
{
    protected $signature = 'postcode:import {--strategy=db : The strategy to use for import (db, split, or csv)}';
    protected $description = 'Import UK postcodes from a CSV file using the specified strategy';

    public function handle()
    {
        // Fetch the context and pass the user input (strategy option)
        $context = app(UKPostcodeImportContext::class);
        $strategy = $this->option('strategy');

        if (!in_array($strategy, ['db', 'split', 'csv'])) {
            $this->error('Invalid strategy option. Choose from db, split, or csv.');
            return 1;
        }

        $context->executeImport($strategy);

        $this->info('Postcode import completed.');
        return 0;
    }
}
