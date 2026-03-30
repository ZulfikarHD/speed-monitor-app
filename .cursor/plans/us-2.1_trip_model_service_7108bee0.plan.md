---
name: US-2.1 Trip Model Service
overview: Implement Trip and SpeedLog models with business logic in TripService following Laravel best practices and the service pattern established in this application. This includes model relationships, factories, and comprehensive tests.
todos:
  - id: create-trip-status-enum
    content: Create TripStatus enum with InProgress, Completed, AutoStopped cases
    status: completed
  - id: create-trip-model
    content: Create Trip model with fillable attributes, casts, and relationships to User and SpeedLog
    status: completed
  - id: create-speedlog-model
    content: Create SpeedLog model with fillable attributes, casts, and relationship to Trip
    status: completed
  - id: update-user-model
    content: Add hasMany trips relationship to User model
    status: completed
  - id: create-trip-service
    content: Create TripService with startTrip, endTrip, updateTripStats, and autoStopTrip methods
    status: completed
  - id: create-trip-factory
    content: Create TripFactory with completed, autoStopped, withViolations, and synced states
    status: completed
  - id: create-speedlog-factory
    content: Create SpeedLogFactory with violation and safe states
    status: completed
  - id: create-service-tests
    content: Create comprehensive unit tests for TripService (all methods, edge cases)
    status: completed
  - id: create-model-tests
    content: Create feature tests for Trip and SpeedLog models (relationships, casts, cascade delete)
    status: completed
  - id: run-tests-and-lint
    content: Run all tests and format code with Pint
    status: completed
isProject: false
---

# Plan for US-2.1: Create Trip Model & Service

## Context

The database migrations for `trips` and `speed_logs` tables already exist (created on 2026-03-30). This story focuses on building the model layer and business logic service following the patterns established in the codebase.

**Existing Patterns to Follow:**
- Service pattern: [`app/Services/AuthService.php`](app/Services/AuthService.php) shows PHPDoc standards and service structure
- Model pattern: [`app/Models/User.php`](app/Models/User.php) uses Laravel 12 attributes (`#[Fillable]`, `#[Hidden]`)
- Factory pattern: [`database/factories/UserFactory.php`](database/factories/UserFactory.php) includes role-specific states
- Test pattern: [`tests/Feature/Auth/AuthenticationTest.php`](tests/Feature/Auth/AuthenticationTest.php) shows PHPUnit structure

## Database Schema Reference

### Trips Table Schema
From [`database/migrations/2026_03_30_161608_create_trips_table.php`](database/migrations/2026_03_30_161608_create_trips_table.php):
- `id`, `user_id` (FK to users), `started_at`, `ended_at` (nullable)
- `status` (enum: in_progress, completed, auto_stopped)
- `total_distance` (decimal, km), `max_speed` (decimal, km/h), `average_speed` (decimal, km/h)
- `violation_count` (integer, default: 0), `duration_seconds` (integer, nullable)
- `notes` (text, nullable), `synced_at` (datetime, nullable)
- Indexes: `[user_id, started_at]`, `status`, `ended_at`

### Speed Logs Table Schema
From [`database/migrations/2026_03_30_161637_create_speed_logs_table.php`](database/migrations/2026_03_30_161637_create_speed_logs_table.php):
- `id`, `trip_id` (FK to trips, cascade delete), `speed` (decimal, km/h)
- `recorded_at` (datetime), `is_violation` (boolean, default: false)
- Indexes: `[trip_id, recorded_at]`, `is_violation`

## Implementation Steps

### 1. Create Trip Model

Create `app/Models/Trip.php` with:

**Attributes & Casts:**
- Use `#[Fillable]` attribute for mass assignment protection
- Cast dates: `started_at`, `ended_at`, `synced_at` → `datetime`
- Cast decimals: `total_distance`, `max_speed`, `average_speed` → `decimal:2`
- Cast integers: `violation_count`, `duration_seconds` → `integer`
- Cast enum: `status` → custom TripStatus enum

**Relationships:**
- `belongsTo(User::class)` - the employee who owns this trip
- `hasMany(SpeedLog::class)` - all speed logs for this trip

**Enum for Status:**
- Create `app/Enums/TripStatus.php` enum with cases: InProgress, Completed, AutoStopped
- Follow Laravel 12 enum patterns

### 2. Create SpeedLog Model

Create `app/Models/SpeedLog.php` with:

**Attributes & Casts:**
- Use `#[Fillable]` for mass assignment
- Cast: `speed` → `decimal:2`, `is_violation` → `boolean`, `recorded_at` → `datetime`

**Relationships:**
- `belongsTo(Trip::class)` - parent trip relationship

**Note:** SpeedLog uses `created_at` only (no `updated_at` per migration)

### 3. Update User Model Relationships

Update [`app/Models/User.php`](app/Models/User.php):
- Add `hasMany(Trip::class)` relationship method
- Maintains existing role helper methods (isEmployee, isSupervisor, isAdmin)

### 4. Create TripService

Create `app/Services/TripService.php` following the pattern in [`app/Services/AuthService.php`](app/Services/AuthService.php):

**Method: `startTrip(User $user, ?string $notes = null): Trip`**
- Create new trip with status InProgress
- Set `started_at` to current timestamp (Asia/Jakarta timezone)
- Associate with authenticated user
- Return created Trip model

**Method: `endTrip(Trip $trip): Trip`**
- Validate trip status is InProgress
- Calculate and update trip statistics:
  - `ended_at` = current timestamp
  - `duration_seconds` = ended_at - started_at (in seconds)
  - Call `updateTripStats()` internally
- Set status to Completed
- Return updated Trip model

**Method: `updateTripStats(Trip $trip): Trip`**
- Load all speed logs for the trip
- Calculate statistics:
  - `max_speed` = max speed from logs
  - `average_speed` = average of all speeds
  - `violation_count` = count of logs where is_violation = true
  - `total_distance` = calculated from speed logs (speed × time intervals)
- Distance calculation logic: sum of (speed × 5 seconds) for each log, convert to km
- Return updated Trip model

**Method: `autoStopTrip(Trip $trip): Trip`**
- Similar to endTrip but sets status to AutoStopped
- Use for trips that exceed inactivity duration (30 min by default)
- Will be called by scheduled job in future sprint

**PHPDoc Standards:**
- Comprehensive method docblocks with `@param`, `@return`, `@throws` tags
- Array shape definitions where applicable
- Service-level docblock explaining purpose and dependencies

### 5. Create Model Factories

**TripFactory** (`database/factories/TripFactory.php`):
- Default state: creates in_progress trip with started_at = now()
- State methods:
  - `completed()`: sets status=completed, ended_at, with calculated stats
  - `autoStopped()`: sets status=auto_stopped
  - `withViolations()`: creates trip with violation_count > 0
  - `synced()`: sets synced_at timestamp (for offline sync feature)

**SpeedLogFactory** (`database/factories/SpeedLogFactory.php`):
- Default: random speed between 0-80 km/h, recorded_at = now()
- State methods:
  - `violation()`: speed above speed_limit (fetch from settings, default 60 km/h)
  - `safe()`: speed below speed_limit

### 6. Create Comprehensive Tests

**Unit Tests** - `tests/Unit/Services/TripServiceTest.php`:
- Test `startTrip()`:
  - Creates trip with correct user association
  - Sets status to InProgress
  - Sets started_at correctly
- Test `endTrip()`:
  - Updates ended_at and status
  - Calculates duration_seconds
  - Calls updateTripStats
- Test `updateTripStats()`:
  - Calculates max_speed from logs
  - Calculates average_speed correctly
  - Counts violations accurately
  - Calculates total_distance based on speed/time
- Test `autoStopTrip()`:
  - Sets status to AutoStopped
  - Preserves all statistics

**Feature/Model Tests** - `tests/Feature/Models/TripTest.php`:
- Test Trip model relationships:
  - belongsTo User
  - hasMany SpeedLog
- Test Trip casting:
  - Date attributes cast correctly
  - Decimal attributes have 2 decimal places
  - Status enum works correctly
- Test SpeedLog model relationships:
  - belongsTo Trip
- Test cascade deletion:
  - Deleting trip deletes associated speed_logs

## Testing Requirements

Per Laravel Boost guidelines:
1. Use factories for test data (no manual model creation)
2. Use `RefreshDatabase` trait
3. Test both happy paths and edge cases
4. Run tests after implementation: `php artisan test --filter=Trip`

## Key Decisions

**Distance Calculation Logic:**
- Speed logs are recorded every 5 seconds (from ARCHITECTURE.md)
- Distance for each log = (speed in km/h × 5 seconds) / 3600 seconds/hour
- Total distance = sum of all segment distances
- Example: speed=60 km/h for 5 seconds = 60 × (5/3600) = 0.0833 km (83.3 meters)

**Settings Integration:**
- TripService needs access to `speed_limit` setting from settings table
- For now, we'll hardcode 60 km/h for is_violation calculation
- US-2.5 will implement SettingsService properly

**Timezone:**
- All timestamps use Asia/Jakarta (WIB) per user requirements
- Ensure timestamps are stored in UTC in database, displayed in WIB

## Files to Create/Modify

**Create:**
- `app/Enums/TripStatus.php`
- `app/Models/Trip.php`
- `app/Models/SpeedLog.php`
- `app/Services/TripService.php`
- `database/factories/TripFactory.php`
- `database/factories/SpeedLogFactory.php`
- `tests/Unit/Services/TripServiceTest.php`
- `tests/Feature/Models/TripTest.php`

**Modify:**
- `app/Models/User.php` (add trips relationship)

## Acceptance Criteria Checklist

- [ ] Trip model created with relationships (belongsTo User, hasMany SpeedLog)
- [ ] SpeedLog model created with relationship (belongsTo Trip)
- [ ] TripService created with service pattern
- [ ] Methods implemented: startTrip, endTrip, updateTripStats, autoStopTrip
- [ ] Calculates max speed correctly from speed logs
- [ ] Calculates average speed correctly from speed logs
- [ ] Calculates total distance from speed logs (km)
- [ ] Violation count calculated from is_violation flags
- [ ] Duration calculated from started_at to ended_at
- [ ] Factories created for Trip and SpeedLog with useful states
- [ ] Comprehensive unit tests for TripService
- [ ] Feature tests for Trip and SpeedLog models
- [ ] All tests passing: `php artisan test --filter=Trip`
- [ ] Code formatted with Pint: `vendor/bin/pint --dirty`

## Dependencies

**Required for this story:**
- Settings table exists but Setting model not yet created (acceptable - hardcode speed_limit=60 for now)

**Blocked by:** None (migrations already exist)

**Blocks:** US-2.2 (can be done in parallel), US-2.3 (TripController needs TripService)
