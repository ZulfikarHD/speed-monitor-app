<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    // ========================================================================
    // Profile Page Access Tests
    // ========================================================================

    public function test_authenticated_user_can_view_profile_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Profile')
                ->has('user')
            );
    }

    public function test_unauthenticated_user_cannot_access_profile(): void
    {
        $response = $this->get('/profile');

        $response->assertRedirect('/login');
    }

    // ========================================================================
    // Profile Information Update Tests
    // ========================================================================

    public function test_user_can_update_name(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'test@example.com',
        ]);

        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'New Name',
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHas('success', 'Profile updated successfully');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'test@example.com',
        ]);
    }

    public function test_user_can_update_email(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'old@example.com',
        ]);

        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'Test User',
            'email' => 'new@example.com',
        ]);

        $response->assertSessionHas('success', 'Profile updated successfully');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'new@example.com',
        ]);
    }

    public function test_user_can_update_both_name_and_email(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

        $response->assertSessionHas('success', 'Profile updated successfully');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);
    }

    public function test_user_cannot_use_existing_email(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_name_is_required_for_profile_update(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/profile', [
            'name' => '',
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_email_is_required_for_profile_update(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'Test User',
            'email' => '',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_email_must_be_valid_format(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'Test User',
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_name_cannot_exceed_255_characters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/profile', [
            'name' => str_repeat('a', 256),
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    // ========================================================================
    // Password Change Tests
    // ========================================================================

    public function test_user_can_change_password_with_correct_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'old-password',
            'new_password' => 'new-password',
            'new_password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHas('success', 'Password changed successfully');

        // Verify new password works
        $user->refresh();
        $this->assertTrue(Hash::check('new-password', $user->password));
    }

    public function test_user_cannot_change_password_with_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'wrong-password',
            'new_password' => 'new-password',
            'new_password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasErrors(['current_password']);

        // Verify old password still works
        $user->refresh();
        $this->assertTrue(Hash::check('old-password', $user->password));
    }

    public function test_new_password_requires_confirmation(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'old-password',
            'new_password' => 'new-password',
            'new_password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors(['new_password']);
    }

    public function test_new_password_must_be_at_least_8_characters(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'old-password',
            'new_password' => 'short',
            'new_password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors(['new_password']);
    }

    public function test_current_password_is_required_for_password_change(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/profile/password', [
            'current_password' => '',
            'new_password' => 'new-password',
            'new_password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasErrors(['current_password']);
    }

    public function test_new_password_is_required_for_password_change(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'old-password',
            'new_password' => '',
            'new_password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors(['new_password']);
    }

    // ========================================================================
    // Authorization Tests
    // ========================================================================

    public function test_all_roles_can_access_profile_page(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $superuser = User::factory()->create(['role' => 'superuser']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($employee)->get('/profile')->assertStatus(200);
        $this->actingAs($superuser)->get('/profile')->assertStatus(200);
        $this->actingAs($admin)->get('/profile')->assertStatus(200);
    }

    public function test_user_can_only_update_their_own_profile(): void
    {
        $user1 = User::factory()->create(['name' => 'User One']);
        $user2 = User::factory()->create(['name' => 'User Two']);

        // User 1 updates their profile
        $this->actingAs($user1)->put('/profile', [
            'name' => 'Updated Name',
            'email' => $user1->email,
        ]);

        // User 1's name should be updated
        $this->assertDatabaseHas('users', [
            'id' => $user1->id,
            'name' => 'Updated Name',
        ]);

        // User 2's name should remain unchanged
        $this->assertDatabaseHas('users', [
            'id' => $user2->id,
            'name' => 'User Two',
        ]);
    }
}
