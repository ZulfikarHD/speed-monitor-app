<?php

namespace Tests\Feature\Superuser;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Employee Management Feature Tests (Superuser)
 *
 * Tests superuser user management functionality including authorization,
 * CRUD operations, search, filtering, and validation.
 */
class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test superuser can view employees list page.
     */
    public function test_superuser_can_view_employees_list(): void
    {
        $superuser = User::factory()->superuser()->create();
        $employees = User::factory()->employee()->count(5)->create();

        $response = $this->actingAs($superuser)
            ->get(route('superuser.employees'));

        $response->assertOk();
        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('superuser/Employees')
                ->has('users', 6)
                ->has('meta')
                ->has('filters')
        );
    }

    /**
     * Test employee cannot access employees page.
     */
    public function test_employee_cannot_access_employees_page(): void
    {
        $employee = User::factory()->employee()->create();

        $response = $this->actingAs($employee)
            ->get(route('superuser.employees'));

        $response->assertRedirect();
    }

    /**
     * Test superuser can create new user.
     */
    public function test_superuser_can_create_new_user(): void
    {
        $superuser = User::factory()->superuser()->create();

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'role' => 'employee',
            'is_active' => true,
        ];

        $response = $this->actingAs($superuser)
            ->post(route('superuser.employees.store'), $userData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'employee',
            'is_active' => true,
        ]);
    }

    /**
     * Test superuser can update user details.
     */
    public function test_superuser_can_update_user_details(): void
    {
        $superuser = User::factory()->superuser()->create();
        $employee = User::factory()->employee()->create();

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'superuser',
            'is_active' => true,
        ];

        $response = $this->actingAs($superuser)
            ->put(route('superuser.employees.update', $employee), $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $employee->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'superuser',
        ]);
    }

    /**
     * Test superuser cannot update themselves.
     */
    public function test_superuser_cannot_update_themselves(): void
    {
        $superuser = User::factory()->superuser()->create();

        $updateData = [
            'name' => 'Updated Superuser Name',
            'email' => 'updated@example.com',
            'role' => 'superuser',
        ];

        $response = $this->actingAs($superuser)
            ->put(route('superuser.employees.update', $superuser), $updateData);

        $response->assertForbidden();
    }

    /**
     * Test superuser can deactivate user.
     */
    public function test_superuser_can_deactivate_user(): void
    {
        $superuser = User::factory()->superuser()->create();
        $employee = User::factory()->employee()->create();

        $response = $this->actingAs($superuser)
            ->delete(route('superuser.employees.deactivate', $employee));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $employee->id,
            'is_active' => false,
        ]);
    }

    /**
     * Test superuser cannot deactivate themselves.
     */
    public function test_superuser_cannot_deactivate_themselves(): void
    {
        $superuser = User::factory()->superuser()->create();

        $response = $this->actingAs($superuser)
            ->delete(route('superuser.employees.deactivate', $superuser));

        $response->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $superuser->id,
            'is_active' => true,
        ]);
    }

    /**
     * Test search filters users correctly.
     */
    public function test_search_filters_users_correctly(): void
    {
        $superuser = User::factory()->superuser()->create();
        User::factory()->create(['name' => 'John Smith', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);
        User::factory()->create(['name' => 'Bob Wilson', 'email' => 'bob@example.com']);

        $response = $this->actingAs($superuser)
            ->get(route('superuser.employees', ['search' => 'john']));

        $response->assertOk();
        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('superuser/Employees')
                ->has('users', 1)
                ->where('users.0.name', 'John Smith')
        );
    }

    /**
     * Test role filter works correctly.
     */
    public function test_role_filter_works_correctly(): void
    {
        $superuser = User::factory()->superuser()->create();
        User::factory()->employee()->count(3)->create();
        User::factory()->superuser()->count(2)->create();

        $response = $this->actingAs($superuser)
            ->get(route('superuser.employees', ['role' => 'superuser']));

        $response->assertOk();
        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('superuser/Employees')
                ->has('users', 3)
        );
    }

    /**
     * Test status filter works correctly.
     */
    public function test_status_filter_works_correctly(): void
    {
        $superuser = User::factory()->superuser()->create();
        User::factory()->employee()->count(3)->create();
        User::factory()->employee()->inactive()->count(2)->create();

        $response = $this->actingAs($superuser)
            ->get(route('superuser.employees', ['status' => 'inactive']));

        $response->assertOk();
        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('superuser/Employees')
                ->has('users', 2)
        );
    }

    /**
     * Test validation errors for user creation.
     */
    public function test_validation_errors_for_user_creation(): void
    {
        $superuser = User::factory()->superuser()->create();

        $response = $this->actingAs($superuser)
            ->post(route('superuser.employees.store'), [
                'name' => '',
                'email' => 'invalid-email',
                'password' => '123',
                'role' => 'invalid-role',
            ]);

        $response->assertSessionHasErrors(['name', 'email', 'password', 'role']);
    }

    /**
     * Test email must be unique.
     */
    public function test_email_must_be_unique(): void
    {
        $superuser = User::factory()->superuser()->create();
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($superuser)
            ->post(route('superuser.employees.store'), [
                'name' => 'New User',
                'email' => 'existing@example.com',
                'password' => 'password123',
                'role' => 'employee',
            ]);

        $response->assertSessionHasErrors(['email']);
    }

    /**
     * Test password is optional when updating user.
     */
    public function test_password_is_optional_when_updating_user(): void
    {
        $superuser = User::factory()->superuser()->create();
        $employee = User::factory()->employee()->create();
        $originalPassword = $employee->password;

        $response = $this->actingAs($superuser)
            ->put(route('superuser.employees.update', $employee), [
                'name' => 'Updated Name',
                'email' => $employee->email,
                'role' => 'employee',
                'is_active' => true,
            ]);

        $response->assertRedirect();

        $employee->refresh();
        $this->assertEquals($originalPassword, $employee->password);
    }

    /**
     * Test password is hashed when provided.
     */
    public function test_password_is_hashed_when_provided(): void
    {
        $superuser = User::factory()->superuser()->create();

        $response = $this->actingAs($superuser)
            ->post(route('superuser.employees.store'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'plaintext123',
                'role' => 'employee',
            ]);

        $response->assertRedirect();

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotEquals('plaintext123', $user->password);
        $this->assertTrue(strlen($user->password) > 20);
    }

    /**
     * Test pagination works correctly.
     */
    public function test_pagination_works_correctly(): void
    {
        $superuser = User::factory()->superuser()->create();
        User::factory()->employee()->count(25)->create();

        $response = $this->actingAs($superuser)
            ->get(route('superuser.employees'));

        $response->assertOk();
        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('superuser/Employees')
                ->has('users', 20)
                ->where('meta.total', 26)
                ->where('meta.last_page', 2)
        );
    }
}
