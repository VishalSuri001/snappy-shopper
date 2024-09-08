<?php

namespace Tests\Feature;

use Database\Seeders\ShoppingStoreSeeder;
use Database\Seeders\ShoppingStoreTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StoreDeliveryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ShoppingStoreTypeSeeder::class);
        $this->seed(ShoppingStoreSeeder::class);
    }

    public function testStoresDeliveringToPostcode()
    {
        $response = $this->get('/search/deliveryStores?latitude=52.5075000&longitude=-1.9910000&postcode=B71 4AD');
        dd($response);
        $response->assertStatus(200);
    }
}
