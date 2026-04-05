<?php

namespace Tests\Feature\Supervisor;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Employee Management Feature Tests (Supervisor)
 *
 * Tests supervisor user management functionality including authorization,
 * CRUD operations, search, filtering, and validation.
 */
class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test supervisor can view employees list page.
     */
    public function test_supervisor_can_view_employees_list(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        $employees = User::factory()->employee()->count(5)->create();

        $response = $this->actingAs($supervisor)
            ->get(route('supervisor.employees'));

        $response->assertOk();
        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('supervisor/Employees')
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
            ->get(route('supervisor.employees'));

        $response->assertRedirect();
    }

    /**
     * Test supervisor can create new user.
     */
    public function test_supervisor_can_create_new_user(): void
    {
        $supervisor = User::factory()->supervisor()->create();

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'role' => 'employee',
            'is_active' => true,
        ];

        $response = $this->actingAs($supervisor)
            ->post(route('supervisor.employees.store'), $userData);

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
     * Test supervisor can update user details.
     */
    public function test_supervisor_can_update_user_details(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        $employee = User::factory()->employee()->create();

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'supervisor',
            'is_active' => true,
        ];

        $response = $this->actingAs($supervisor)
            ->put(route('supervisor.employees.update', $employee), $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $employee->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'supervisor',
        ]);
    }

    /**
     * Test supervisor cannot update themselves.
     */
    public function test_supervisor_cannot_update_themselves(): void
    {
        $supervisor = User::factory()->supervisor()->create();

        $updateData = [
            'name' => 'Updated Supervisor Name',
            'email' => 'updated@example.com',
            'role' => 'supervisor',
        ];

        $response = $this->actingAs($supervisor)
            ->put(route('supervisor.employees.update', $supervisor), $updateData);

        $response->assertForbidden();
    }

    /**
     * Test supervisor can deactivate user.
     */
    public function test_supervisor_can_deactivate_user(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        $employee = User::factory()->employee()->create();

        $response = $this->actingAs($supervisor)
            ->delete(route('supervisor.employees.deactivate', $employee));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $employee->id,
            'is_active' => false,
        ]);
    }

    /**
     * Test supervisor cannot deactivate themselves.
     */
    public function test_supervisor_cannot_deactivate_themselves(): void
    {
        $supervisor = User::factory()->supervisor()->create();

        $response = $this->actingAs($supervisor)
            ->delete(route('supervisor.employees.deactivate', $supervisor));

        $response->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $supervisor->id,
            'is_active' => true,
        ]);
    }

    /**
     * Test search filters users correctly.
     */
    public function test_search_filters_users_correctly(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        User::factory()->create(['name' => 'John Smith', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);
        User::factory()->create(['name' => 'Bob Wilson', 'email' => 'bob@example.com']);

        $response = $this->actingAs($supervisor)
            ->get(route('supervisor.employees', ['search' => 'john']));

        $response->assertOk();
        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('supervisor/Employees')
                ->has('users', 1)
                ->where('users.0.name', 'John Smith')
        );
    }

    /**
     * Test role filter works correctly.
     */
    public function test_role_filter_works_correctly(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        User::factory()->employee()->count(3)->create();
        User::factory()->supervisor()->count(2)->create();

        $response = $this->actingAs($supervisor)
            ->get(route('supervisor.employees', ['role' => 'supervisor']));

        $response->assertOk();
        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('supervisor/Employees')
                ->has('users', 3)
        );
    }

    /**
     * Test status filter works correctly.
     */
    public function test_status_filter_works_correctly(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        User::factory()->employee()->count(3)->create();
        User::factory()->employee()->inactive()->count(2)->create();

        $response = $this->actingAs($supervisor)
            ->get(route('supervisor.employees', ['status' => 'inactive']));

        $response->assertOk();
        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('supervisor/Employees')
                ->has('users', 2)
        );
    }

    /**
     * Test validation errors for user creation.
     */
    public function test_validation_errors_for_user_creation(): void
    {
        $supervisor = User::factory()->supervisor()->create();

        $response = $this->actingAs($supervisor)
            ->post(route('supervisor.employees.store'), [
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
        $supervisor = User::factory()->supervisor()->create();
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($supervisor)
            ->post(route('supervisor.employees.store'), [
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
        $supervisor = User::factory()->supervisor()->create();
        $employee = User::factory()->employee()->create();
        $originalPassword = $employee->password;

        $response = $this->actingAs($supervisor)
            ->put(route('supervisor.employees.update', $employee), [
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
        $supervisor = User::factory()->supervisor()->create();

        $response = $this->actingAs($supervisor)
            ->post(route('supervisor.employees.store'), [
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
        $supervisor = User::factory()->supervisor()->create();
        User::factory()->employee()->count(25)->create();

        $response = $this->actingAs($supervisor)
            ->get(route('supervisor.employees'));

        $response->assertOk();
        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('supervisor/Employees')
                ->has('users', 20)
                ->where('meta.total', 26)
                ->where('meta.last_page', 2)
        );
    }
}
