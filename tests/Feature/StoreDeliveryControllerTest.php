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
        $response = $this->getJson('/search/deliveryStores?latitude=52.5075000&longitude=-1.9910000&postcode=B71 4AD');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'message' => 'Store Found!'
        ]);
        $response->assertJsonStructure([
            'stores' => [
                '*' => [
                    'id',
                    'name',
                    'store_type',
                    'latitude',
                    'longitude',
                    'postcode'
                ]
            ]
        ]);

        $this->assertDatabaseHas('shopping_stores', [
            'postcode' => 'B71 4AD',
        ]);
    }

    public function testNoStoresDeliveringToPostcode()
    {
        DB::table('shopping_stores')->truncate();
        $response = $this->get('/search/deliveryStores?latitude=52.5075000&longitude=-1.9910000&postcode=B71 4AD');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'message' => 'No stores delivering to this postcode'
        ]);
        $response->assertJsonCount(0, 'stores');
    }

    public function testInvalidPostcodeOrCoordinates()
    {

        $response = $this->get('/search/deliveryStores?latitude=invalid&longitude=invalid&postcode=invalid');
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['latitude', 'longitude', 'postcode']);
    }
}
