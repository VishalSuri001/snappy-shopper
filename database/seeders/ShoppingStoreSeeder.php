<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ShoppingStoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Path to the CSV file
        $csvFilePath = storage_path('app/public/west_bromwich_stores.csv');

        // Check if the file exists
        if (!file_exists($csvFilePath)) {
            $this->command->error("CSV file not found at path: $csvFilePath");
            return;
        }

        // Open the CSV file
        $file = fopen($csvFilePath, 'r');

        // Read the header row
        $header = fgetcsv($file);

        // Prepare the data array
        $data = [];

        // Read each row of the CSV
        while (($row = fgetcsv($file)) !== false) {
            $data[] = [
                'name' => $row[0],
                'latitude' => $row[1],
                'longitude' => $row[2],
                'postcode' => $row[3],
                'store_type' => $row[4],
                'delivery_distance' => $row[5],
                'is_open' => strtolower($row[6]) === 'true',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Close the file
        fclose($file);

        // Insert data into the database
        DB::table('shopping_stores')->insert($data);

    }
}
