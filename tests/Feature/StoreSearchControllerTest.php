<?php

namespace Tests\Feature;

use Database\Seeders\ShoppingStoreTypeSeeder;
use Database\Seeders\ShoppingStoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StoreSearchControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ShoppingStoreTypeSeeder::class);
        $this->seed(ShoppingStoreSeeder::class);
    }

    public function testStoreNearBy()
    {
        $response = $this->get('/search/nearByStores?latitude=52.5075&longitude=-1.9910&distance=.10');
        $response->assertStatus(200);
    }
}
