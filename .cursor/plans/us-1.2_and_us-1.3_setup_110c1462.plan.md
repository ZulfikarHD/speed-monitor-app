---
name: US-1.2 and US-1.3 Setup
overview: Complete frontend setup with Pinia state management and create all database migrations for the trip monitoring system.
todos:
  - id: install-pinia
    content: Install Pinia via yarn and configure in app.ts with Inertia
    status: completed
  - id: create-auth-store
    content: Create auth store with user, role, isAuthenticated state
    status: completed
  - id: create-settings-store
    content: Create settings store for app configuration
    status: completed
  - id: update-types
    content: Add Pinia and store types to TypeScript definitions
    status: completed
  - id: modify-users-migration
    content: Create migration to add role and is_active fields to users table
    status: completed
  - id: create-trips-migration
    content: Create trips table migration with all fields, indexes, and foreign keys
    status: completed
  - id: create-speedlogs-migration
    content: Create speed_logs table migration with trip relationship
    status: completed
  - id: create-settings-migration
    content: Create settings table migration for app configuration
    status: completed
  - id: test-migrations
    content: Run migrate, rollback, and migrate again to verify all migrations work correctly
    status: completed
  - id: run-pint
    content: Run vendor/bin/pint to format PHP files
    status: completed
isProject: false
---

# Execution Plan: US-1.2 and US-1.3

## US-1.2: Complete Vue 3 Frontend Setup

### Current Status
Most dependencies are already installed via Laravel Inertia.js starter kit:
- ✅ Vue 3 (v3.5.13)
- ✅ Vite (v8.0.0)
- ✅ Inertia.js v3 (@inertiajs/vue3, @inertiajs/vite)
- ✅ Tailwind CSS v4
- ✅ TypeScript configured
- ✅ Laravel Wayfinder (replaces Ziggy for route generation)

### What Needs to Be Done

#### 1. Install Pinia Store
Install Pinia for centralized state management:

```bash
yarn add pinia
```

#### 2. Configure Pinia in Application
Update [`resources/js/app.ts`](resources/js/app.ts) to:
- Import and create Pinia instance
- Pass it to Inertia app via `resolve` function
- Configure proper Vue app initialization

#### 3. Create Initial Store Structure
Set up the stores directory with initial stores:
- `resources/js/stores/auth.ts` - Authentication state (user, role, token)
- `resources/js/stores/settings.ts` - Application settings (speed limit, etc.)

#### 4. Update TypeScript Types
Add Pinia types to [`resources/js/types/index.ts`](resources/js/types/index.ts) for type safety.

**Note:** Vue Router is NOT needed (Inertia handles routing). Axios is NOT needed (Inertia has built-in HTTP client via `useForm()` and `router` methods). PWA functionality will be added in Sprint 5 as originally planned.

---

## US-1.3: Create Database Schema

### Overview
Create migrations for all database tables following the schema defined in [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md).

### Migrations to Create

#### 1. Modify Users Table
**File:** `database/migrations/YYYY_MM_DD_HHMMSS_add_role_and_status_to_users_table.php`

Add to existing users table:
- `role` enum('employee', 'supervisor', 'admin') - User role
- `is_active` boolean default true - Account status

Use Laravel's `php artisan make:migration` command.

#### 2. Create Trips Table
**File:** `database/migrations/YYYY_MM_DD_HHMMSS_create_trips_table.php`

Columns:
- `id` (bigint, PK)
- `user_id` (bigint, FK -> users.id)
- `started_at` (datetime)
- `ended_at` (datetime, nullable)
- `status` enum('in_progress', 'completed', 'auto_stopped')
- `total_distance` (decimal(10,2), nullable) - in kilometers
- `max_speed` (decimal(5,2), nullable) - in km/h
- `average_speed` (decimal(5,2), nullable) - in km/h
- `violation_count` (integer, default 0)
- `duration_seconds` (integer, nullable)
- `notes` (text, nullable)
- `synced_at` (datetime, nullable) - for offline sync tracking
- `timestamps`

Indexes:
- `user_id, started_at` (compound index for filtering)
- `status` (for active trip queries)
- `ended_at` (for date range filtering)

Foreign key: `user_id` references `users.id` with `onDelete('cascade')`

#### 3. Create Speed Logs Table
**File:** `database/migrations/YYYY_MM_DD_HHMMSS_create_speed_logs_table.php`

Columns:
- `id` (bigint, PK)
- `trip_id` (bigint, FK -> trips.id)
- `speed` (decimal(5,2)) - in km/h
- `recorded_at` (datetime) - when speed was recorded
- `is_violation` (boolean, default false) - exceeds speed limit
- `created_at` (timestamp only - no updated_at needed)

Indexes:
- `trip_id, recorded_at` (compound index for chronological queries)
- `is_violation` (for violation statistics)

Foreign key: `trip_id` references `trips.id` with `onDelete('cascade')`

**Note:** This table will grow large. Each 1-hour trip at 5-second intervals = 720 records.

#### 4. Create Settings Table
**File:** `database/migrations/YYYY_MM_DD_HHMMSS_create_settings_table.php`

Columns:
- `id` (bigint, PK)
- `key` (string, unique) - setting name
- `value` (text) - setting value (stored as string, cast in code)
- `description` (text, nullable) - human-readable description
- `timestamps`

Default settings to seed:
- `speed_limit`: 60 (km/h)
- `auto_stop_duration`: 1800 (seconds = 30 minutes)
- `speed_log_interval`: 5 (seconds)
- `violation_threshold`: 60 (km/h)

### Testing Migrations

After creating migrations:
1. Run `php artisan migrate` to test up()
2. Run `php artisan migrate:rollback` to test down()
3. Run `php artisan migrate` again to verify idempotency
4. Check database schema matches [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) specifications

### Implementation Order

1. **First:** Modify users table (depends on nothing)
2. **Second:** Create trips table (depends on users)
3. **Third:** Create speed_logs table (depends on trips)
4. **Fourth:** Create settings table (independent)

---

## Files to Create/Modify

### US-1.2 Files
- `resources/js/app.ts` (modify - add Pinia)
- `resources/js/stores/auth.ts` (create)
- `resources/js/stores/settings.ts` (create)
- `resources/js/types/index.ts` (modify - add store types)
- `package.json` (modify - add pinia dependency)

### US-1.3 Files
- `database/migrations/*_add_role_and_status_to_users_table.php` (create)
- `database/migrations/*_create_trips_table.php` (create)
- `database/migrations/*_create_speed_logs_table.php` (create)
- `database/migrations/*_create_settings_table.php` (create)

---

## Verification Checklist

### US-1.2 Verification
- [ ] Pinia installed in package.json
- [ ] Pinia configured in app.ts
- [ ] Auth store created with user, role, isAuthenticated state
- [ ] Settings store created with speed_limit, auto_stop_duration
- [ ] No ESLint or TypeScript errors
- [ ] Application runs without console errors

### US-1.3 Verification
- [ ] All 4 migrations created
- [ ] Migrations run successfully (php artisan migrate)
- [ ] Migrations rollback successfully (php artisan migrate:rollback)
- [ ] Database schema matches ARCHITECTURE.md
- [ ] All foreign keys properly defined
- [ ] All indexes created on specified columns
- [ ] Enum values match specifications

---

## Technical Notes

### Inertia.js Architecture
- Uses server-side routing (no Vue Router needed)
- Built-in HTTP client via `useForm()` and `router.get/post/put/delete()`
- Props passed from Laravel controllers to Vue pages
- Wayfinder generates typed route functions: `import { index } from '@/actions/TripController'`

### Timezone Consideration
All datetime fields will use the application's default timezone (Asia/Jakarta - WIB) as specified in user rules.

### Database Performance
- Compound indexes on frequently filtered columns (user_id + started_at)
- Foreign key constraints with cascade delete for data integrity
- speed_logs table will be the largest - consider partitioning in future if > 1M rows
