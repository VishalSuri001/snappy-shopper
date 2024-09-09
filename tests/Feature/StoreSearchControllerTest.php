<?php

namespace Tests\Feature;

use App\Models\ShoppingStoreType;
use Database\Seeders\ShoppingStoreTypeSeeder;
use Database\Seeders\ShoppingStoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class StoreSearchControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Truncate relevant tables to reset auto-increment values
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('shopping_store_types')->truncate();
        DB::table('shopping_stores')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->seed(ShoppingStoreTypeSeeder::class);
        $this->seed(ShoppingStoreSeeder::class);
    }

    public function testStoreNearBy()
    {
        $response = $this->get('/search/nearByStores?latitude=52.5075&longitude=-1.9910&distance=.10');
        $response->assertStatus(200);
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
            'latitude' => 52.5075,
            'longitude' => -1.9910,
        ]);
    }

    public function testNoStoreNearBy()
    {
        DB::table('shopping_stores')->truncate();

        $response = $this->getJson('/search/nearByStores?latitude=52.5075&longitude=-1.9910&distance=0.10');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'message' => 'No nearby stores found',
        ]);

        $response->assertJsonCount(0, 'stores');
    }

    public function testInvalidCoordinatesOrDistance()
    {
        $response = $this->getJson('/search/nearByStores?latitude=invalid&longitude=invalid&distance=invalid');
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['latitude', 'longitude', 'distance']);
    }
}
