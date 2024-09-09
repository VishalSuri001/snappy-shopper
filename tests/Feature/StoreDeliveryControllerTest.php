<?php

namespace Tests\Feature;

use App\Models\ShoppingStoreType;
use Database\Seeders\ShoppingStoreSeeder;
use Database\Seeders\ShoppingStoreTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class StoreDeliveryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Truncate relevant tables to reset auto-increment values
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');  // Disable foreign key checks
        DB::table('shopping_store_types')->truncate();
        DB::table('shopping_stores')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->seed(ShoppingStoreTypeSeeder::class);
        $this->seed(ShoppingStoreSeeder::class);
    }

    public function testStoresDeliveringToPostcode()
    {
        $response = $this->get('/search/deliveryStores?latitude=52.5075000&longitude=-1.9910000&postcode=B71 4AD');
        $response->assertStatus(200);
    }
}
