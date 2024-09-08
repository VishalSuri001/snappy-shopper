<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShoppingStoreTypeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('shopping_store_types')->insert([
            ['type_name' => 'takeaway', 'created_at' => now(), 'updated_at' => now()],
            ['type_name' => 'shop', 'created_at' => now(), 'updated_at' => now()],
            ['type_name' => 'restaurant', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
