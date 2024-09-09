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
    }
}
