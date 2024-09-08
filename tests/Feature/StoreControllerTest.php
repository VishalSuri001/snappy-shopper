<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Database\Seeders\ShoppingStoreTypeSeeder;

class StoreControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ShoppingStoreTypeSeeder::class);
    }

    public function testAddStoreWithValidData()
    {
        $response = $this->postJson('/stores', [
            'name' => 'Test Store',
            'postcode' => 'B71 4AD',
            'latitude' => 51.5074,
            'longitude' => -0.1278,
            'is_open' => true,
            'store_type' => 1,
            'delivery_distance' => 4.10,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Store added successfully!',
                'store' => [
                    'name' => 'Test Store',
                    'postcode' => 'B71 4AD',
                    'latitude' => 51.5074,
                    'longitude' => -0.1278,
                    'is_open' => true,
                    'store_type' => 1,
                    'delivery_distance' => 4.10,
                ],
            ]);

        $this->assertDatabaseHas('shopping_stores', [
            'name' => 'Test Store',
            'postcode' => 'B71 4AD',
            'latitude' => 51.5074,
            'longitude' => -0.1278,
            'is_open' => true,
            'store_type' => 1,
            'delivery_distance' => 4.10,
        ]);
    }

    public function testAddStoreWithInvalidData()
    {
        $response = $this->postJson('/stores', [
            'postcode' => 'B71 4AD',
            'latitude' => 51.5074,
            'longitude' => -0.1278,
            'is_open' => true,
            'store_type' => 1,
            'delivery_distance' => 4.10,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        $response = $this->postJson('/stores', [
            'name' => 'Test Store',
            'postcode' => 'B71 4AD',
            'latitude' => 100.0000,
            'longitude' => -0.1278,
            'is_open' => true,
            'store_type' => 1,
            'delivery_distance' => 4.10,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['latitude']);

        $response = $this->postJson('/stores', [
            'name' => 'Test Store',
            'postcode' => 'B71 4AD',
            'latitude' => 51.5074,
            'longitude' => -0.1278,
            'is_open' => true,
            'delivery_distance' => 4.10,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['store_type']);
    }
}
