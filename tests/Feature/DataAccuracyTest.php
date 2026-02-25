<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\HistoricalTide;

class DataAccuracyTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_no_data_when_db_is_empty()
    {
        $user = User::factory()->create();

        // Ensure DB is empty
        HistoricalTide::query()->delete();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Belum Ada Data Prediksi');
        $response->assertDontSee('Tinggi Air (KNN)'); // Validasi chart/stats hidden
    }

    public function test_api_returns_empty_status_when_db_is_empty()
    {
        $user = User::factory()->create();

        // Ensure DB is empty
        HistoricalTide::query()->delete();

        $response = $this->actingAs($user)->getJson('/api/realtime-data');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'empty',
            'message' => 'No data available'
        ]);
    }
}
