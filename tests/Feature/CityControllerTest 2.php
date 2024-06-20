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
        $response = $this->getJson('/api/cities');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    public function test_store()
    {
        $response = $this->postJson('/api/cities', [
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

        $response = $this->postJson('/api/cities', [
            'name' => 'Invalid City',
        ]);

        $response->assertStatus(404);
        $response->assertJson(['message' => 'City does not exist']);
    }

    public function test_show()
    {
        $city = City::first();

        $response = $this->getJson("/api/cities/{$city->id}");

        $response->assertStatus(200);
        $response->assertJson(['name' => $city->name]);
    }

    public function test_show_not_found()
    {
        $response = $this->getJson('/api/cities/999');

        $response->assertStatus(404);
        $response->assertJson(['message' => 'City not found']);
    }

    public function test_update()
    {
        $city = City::first();

        $response = $this->putJson("/api/cities/{$city->id}", [
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
        $response = $this->putJson('/api/cities/999', [
            'name' => 'Updated City',
        ]);

        $response->assertStatus(404);
        $response->assertJson(['message' => 'City not found']);
    }

    public function test_destroy()
    {
        $city = City::first();

        $response = $this->deleteJson("/api/cities/{$city->id}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'City deleted successfully']);
        $this->assertDatabaseMissing('cities', ['id' => $city->id]);
    }

    public function test_destroy_not_found()
    {
        $response = $this->deleteJson('/api/cities/999');

        $response->assertStatus(404);
        $response->assertJson(['message' => 'City not found']);
    }

    public function test_verify_city()
    {
        $response = $this->getJson('/api/cities/verify/Test City');

        $response->assertStatus(200);
        $response->assertJson(['valid' => true]);
    }

    public function test_verify_city_not_found()
    {
        Http::fake([
            'api.geonames.org/searchJSON*' => Http::response([
                'totalResultsCount' => 0,
            ], 200),
        ]);

        $response = $this->getJson('/api/cities/verify/Invalid City');

        $response->assertStatus(404);
        $response->assertJson(['valid' => false, 'message' => 'City not found']);
    }
}
