<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class CityControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up necessary data
        City::factory()->create([
            'name' => 'Test City',
            'favorite' => true,
            'temperature' => 25.5,
        ]);

        // Mock the GeoNames API responses
        Http::fake([
            'api.geonames.org/searchJSON*' => Http::response([
                'totalResultsCount' => 1,
                'geonames' => [
                    [
                        'name' => 'Test City',
                        'countryName' => 'Test Country'
                    ]
                ]
            ], 200),
        ]);
    }

    public function test_index()
    {
        $response = $this->getJson('/cities');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    public function test_store()
    {
        $response = $this->postJson('/cities', [
            'name' => 'New City',
            'favorite' => false,
            'temperature' => 30.0,
        ]);

        $response->assertStatus(201);
        $response->assertJson(['message' => 'City added successfully']);
        $this->assertDatabaseHas('cities', ['name' => 'New City']);
    }

    public function test_store_invalid_city()
    {
        Http::fake([
            'api.geonames.org/searchJSON*' => Http::response([
                'totalResultsCount' => 0,
            ], 200),
        ]);

        $response = $this->postJson('/cities', [
            'name' => 'Invalid City',
        ]);

        $response->assertStatus(500);
        $response->assertJson(['error' => 'Failed to add city']);
    }

    public function test_show()
    {
        $city = City::first();

        $response = $this->getJson("/cities/{$city->id}");

        $response->assertStatus(200);
        $response->assertJson(['name' => $city->name]);
    }

    public function test_show_not_found()
    {
        $response = $this->getJson('/cities/999');

        $response->assertStatus(404);
        $response->assertJson(['message' => 'City not found']);
    }

    public function test_update()
    {
        $city = City::first();

        $response = $this->putJson("/cities/{$city->id}", [
            'name' => 'Updated City',
            'favorite' => false,
            'temperature' => 20.0,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'City updated successfully']);
        $this->assertDatabaseHas('cities', ['name' => 'Updated City']);
    }

    public function test_update_not_found()
    {
        $response = $this->putJson('/cities/999', [
            'name' => 'Updated City',
        ]);

        $response->assertStatus(404);
        $response->assertJson(['message' => 'City not found']);
    }

    public function test_destroy()
    {
        $city = City::first();

        $response = $this->deleteJson("/cities/{$city->id}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'City deleted successfully']);
        $this->assertDatabaseMissing('cities', ['id' => $city->id]);
    }

    public function test_destroy_not_found()
    {
        $response = $this->deleteJson('/cities/999');

        $response->assertStatus(404);
     
        $response->assertJson(['message' => 'City not found']);
    }
}
