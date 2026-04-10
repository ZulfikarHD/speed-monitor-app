<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed default settings for testing
        Setting::set('speed_limit', '60', 'Global speed limit in km/h');
        Setting::set('auto_stop_duration', '1800', 'Auto-stop trip after inactivity (seconds)');
        Setting::set('speed_log_interval', '5', 'Speed logging interval (seconds)');
    }

    public function test_authenticated_user_can_view_settings(): void
    {
        $user = User::factory()->employee()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/settings');

        $response->assertStatus(200)
            ->assertJsonStructure(['data'])
            ->assertJson([
                'data' => [
                    'speed_limit' => '60',
                    'auto_stop_duration' => '1800',
                    'speed_log_interval' => '5',
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_view_settings(): void
    {
        $response = $this->getJson('/api/settings');

        $response->assertStatus(401);
    }

    public function test_admin_can_update_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson('/api/settings', [
                'speed_limit' => 70,
                'auto_stop_duration' => 2400,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Settings updated successfully',
                'data' => [
                    'speed_limit' => '70',
                    'auto_stop_duration' => '2400',
                    'speed_log_interval' => '5', // Unchanged
                ],
            ]);

        // Verify database was updated
        $this->assertEquals('70', Setting::get('speed_limit'));
        $this->assertEquals('2400', Setting::get('auto_stop_duration'));
    }

    public function test_superuser_cannot_update_settings(): void
    {
        $superuser = User::factory()->superuser()->create();

        $response = $this->actingAs($superuser, 'sanctum')
            ->putJson('/api/settings', [
                'speed_limit' => 70,
            ]);

        $response->assertStatus(403);

        // Verify settings were not changed
        $this->assertEquals('60', Setting::get('speed_limit'));
    }

    public function test_employee_cannot_update_settings(): void
    {
        $employee = User::factory()->employee()->create();

        $response = $this->actingAs($employee, 'sanctum')
            ->putJson('/api/settings', [
                'speed_limit' => 70,
            ]);

        $response->assertStatus(403);

        // Verify settings were not changed
        $this->assertEquals('60', Setting::get('speed_limit'));
    }

    public function test_unauthenticated_user_cannot_update_settings(): void
    {
        $response = $this->putJson('/api/settings', [
            'speed_limit' => 70,
        ]);

        $response->assertStatus(401);
    }

    public function test_update_settings_validates_speed_limit_range(): void
    {
        $admin = User::factory()->admin()->create();

        // Test below minimum
        $response = $this->actingAs($admin, 'sanctum')
            ->putJson('/api/settings', [
                'speed_limit' => 0,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['speed_limit']);

        // Test above maximum
        $response = $this->actingAs($admin, 'sanctum')
            ->putJson('/api/settings', [
                'speed_limit' => 201,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['speed_limit']);
    }

    public function test_update_settings_validates_auto_stop_duration_range(): void
    {
        $admin = User::factory()->admin()->create();

        // Test below minimum (less than 1 minute)
        $response = $this->actingAs($admin, 'sanctum')
            ->putJson('/api/settings', [
                'auto_stop_duration' => 59,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['auto_stop_duration']);

        // Test above maximum (more than 2 hours)
        $response = $this->actingAs($admin, 'sanctum')
            ->putJson('/api/settings', [
                'auto_stop_duration' => 7201,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['auto_stop_duration']);
    }

    public function test_update_settings_validates_speed_log_interval_range(): void
    {
        $admin = User::factory()->admin()->create();

        // Test below minimum
        $response = $this->actingAs($admin, 'sanctum')
            ->putJson('/api/settings', [
                'speed_log_interval' => 0,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['speed_log_interval']);

        // Test above maximum
        $response = $this->actingAs($admin, 'sanctum')
            ->putJson('/api/settings', [
                'speed_log_interval' => 61,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['speed_log_interval']);
    }

    public function test_update_settings_allows_partial_updates(): void
    {
        $admin = User::factory()->admin()->create();

        // Update only speed_limit
        $response = $this->actingAs($admin, 'sanctum')
            ->putJson('/api/settings', [
                'speed_limit' => 80,
            ]);

        $response->assertStatus(200);

        // Verify only speed_limit was changed
        $this->assertEquals('80', Setting::get('speed_limit'));
        $this->assertEquals('1800', Setting::get('auto_stop_duration'));
        $this->assertEquals('5', Setting::get('speed_log_interval'));
    }

    public function test_update_settings_accepts_numeric_strings(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson('/api/settings', [
                'speed_limit' => '75',
                'auto_stop_duration' => '3600',
            ]);

        $response->assertStatus(200);

        $this->assertEquals('75', Setting::get('speed_limit'));
        $this->assertEquals('3600', Setting::get('auto_stop_duration'));
    }

    public function test_update_settings_validates_data_types(): void
    {
        $admin = User::factory()->admin()->create();

        // Test non-numeric speed_limit
        $response = $this->actingAs($admin, 'sanctum')
            ->putJson('/api/settings', [
                'speed_limit' => 'invalid',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['speed_limit']);

        // Test non-integer auto_stop_duration
        $response = $this->actingAs($admin, 'sanctum')
            ->putJson('/api/settings', [
                'auto_stop_duration' => 'invalid',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['auto_stop_duration']);

        // Test non-integer speed_log_interval
        $response = $this->actingAs($admin, 'sanctum')
            ->putJson('/api/settings', [
                'speed_log_interval' => 'invalid',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['speed_log_interval']);
    }
}
