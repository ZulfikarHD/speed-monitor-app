---
name: US-2.3 Trip API Endpoints
overview: Implement Trip API endpoints with TripController, authorization policies, form request validation, and comprehensive feature tests following Laravel best practices and existing project patterns.
todos:
  - id: create-trip-policy
    content: Create TripPolicy with viewAny, view, create, update, delete methods and register in AppServiceProvider
    status: completed
  - id: create-form-requests
    content: Create StartTripRequest, EndTripRequest, and ListTripsRequest with validation rules
    status: completed
  - id: create-trip-controller
    content: Create TripController with index, store, show, update methods using TripService
    status: completed
  - id: register-api-routes
    content: Register trip routes in routes/api.php with auth:sanctum middleware
    status: completed
  - id: create-feature-tests
    content: Create TripControllerTest with comprehensive test coverage (26+ tests)
    status: completed
  - id: run-tests-and-lint
    content: Run php artisan test and vendor/bin/pint to verify all tests pass and code is formatted
    status: completed
isProject: false
---

# Plan: US-2.3 - Trip API Endpoints

## Context

This user story implements the core API endpoints for trip management, enabling employees to start/end trips and supervisors to monitor all trips. We'll leverage the existing Trip model, TripService, and SpeedLogService built in US-2.1 and US-2.2.

**Existing Foundation:**
- Trip model: [`app/Models/Trip.php`](app/Models/Trip.php) ✅
- TripService: [`app/Services/TripService.php`](app/Services/TripService.php) with `startTrip()`, `endTrip()`, `updateTripStats()` ✅
- SpeedLog model: [`app/Models/SpeedLog.php`](app/Models/SpeedLog.php) ✅
- SpeedLogService: [`app/Services/SpeedLogService.php`](app/Services/SpeedLogService.php) ✅
- User model with role methods: [`app/Models/User.php`](app/Models/User.php) ✅

**Code Patterns to Follow:**
- Controller: [`app/Http/Controllers/Auth/AuthController.php`](app/Http/Controllers/Auth/AuthController.php) - PHPDoc, service injection, JSON responses
- Form Request: [`app/Http/Requests/Auth/LoginRequest.php`](app/Http/Requests/Auth/LoginRequest.php) - validation rules structure
- Tests: [`tests/Feature/Auth/AuthenticationTest.php`](tests/Feature/Auth/AuthenticationTest.php) - test naming, assertions

## API Endpoints Design

### 1. POST /api/trips - Start New Trip

**Request:**
```json
{
  "notes": "Optional trip notes" // optional
}
```

**Authorization:** Authenticated employees only  
**Business Logic:** Call `TripService::startTrip($user, $notes)`  
**Response (201):**
```json
{
  "trip": {
    "id": 1,
    "user_id": 5,
    "started_at": "2026-03-31T10:00:00Z",
    "status": "in_progress",
    "notes": "Optional trip notes"
  }
}
```

**Validation:**
- Only one active trip per user (business rule check)
- Notes: optional, string, max 1000 chars

---

### 2. PUT /api/trips/{id} - End Trip

**Request:**
```json
{
  "notes": "Optional final notes" // optional
}
```

**Authorization:** Owner of the trip only  
**Business Logic:** Call `TripService::endTrip($trip)`, update notes if provided  
**Response (200):**
```json
{
  "trip": {
    "id": 1,
    "user_id": 5,
    "started_at": "2026-03-31T10:00:00Z",
    "ended_at": "2026-03-31T11:30:00Z",
    "status": "completed",
    "duration_seconds": 5400,
    "max_speed": 75.5,
    "average_speed": 45.2,
    "total_distance": 68.5,
    "violation_count": 3,
    "notes": "Optional final notes"
  }
}
```

**Validation:**
- Trip must exist and belong to authenticated user
- Trip must be in `in_progress` status
- Notes: optional, string, max 1000 chars

---

### 3. GET /api/trips - List Trips (Paginated, Filtered)

**Query Parameters:**
- `page` (integer, default: 1)
- `per_page` (integer, default: 20, max: 100)
- `user_id` (integer, optional) - supervisor/admin only
- `status` (string, optional) - in_progress, completed, auto_stopped
- `date_from` (date, optional) - filter by started_at >= date
- `date_to` (date, optional) - filter by started_at <= date

**Authorization:**
- Employees: see only their own trips
- Supervisors/Admins: see all trips, can filter by user_id

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "user_id": 5,
      "user": {
        "id": 5,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "started_at": "2026-03-31T10:00:00Z",
      "ended_at": "2026-03-31T11:30:00Z",
      "status": "completed",
      "duration_seconds": 5400,
      "max_speed": 75.5,
      "average_speed": 45.2,
      "total_distance": 68.5,
      "violation_count": 3
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 50,
    "last_page": 3
  }
}
```

---

### 4. GET /api/trips/{id} - Get Trip Details with Speed Logs

**Authorization:** Owner or supervisor/admin  
**Response (200):**
```json
{
  "trip": {
    "id": 1,
    "user_id": 5,
    "user": {
      "id": 5,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "started_at": "2026-03-31T10:00:00Z",
    "ended_at": "2026-03-31T11:30:00Z",
    "status": "completed",
    "duration_seconds": 5400,
    "max_speed": 75.5,
    "average_speed": 45.2,
    "total_distance": 68.5,
    "violation_count": 3,
    "notes": "Trip notes",
    "speed_logs": [
      {
        "id": 1,
        "speed": 45.0,
        "recorded_at": "2026-03-31T10:00:00Z",
        "is_violation": false
      },
      {
        "id": 2,
        "speed": 75.5,
        "recorded_at": "2026-03-31T10:00:05Z",
        "is_violation": true
      }
    ]
  }
}
```

---

## Implementation Steps

### 1. Create TripPolicy for Authorization

**File:** `app/Policies/TripPolicy.php`

**Methods:**
- `viewAny(User $user)` - employees can view their own trips, supervisors/admins can view all
- `view(User $user, Trip $trip)` - owner or supervisor/admin can view trip details
- `create(User $user)` - employees can create trips
- `update(User $user, Trip $trip)` - only trip owner can end trip
- `delete(User $user, Trip $trip)` - supervisor/admin only (future use)

**Register Policy:**
Update `app/Providers/AppServiceProvider.php` or `AuthServiceProvider.php` to register the policy:
```php
protected $policies = [
    Trip::class => TripPolicy::class,
];
```

---

### 2. Create Form Request Validation Classes

#### StartTripRequest
**File:** `app/Http/Requests/Trip/StartTripRequest.php`

**Validation Rules:**
```php
[
    'notes' => ['nullable', 'string', 'max:1000'],
]
```

**Additional Validation:**
- Check if user already has an active trip (custom validation or controller check)

---

#### EndTripRequest
**File:** `app/Http/Requests/Trip/EndTripRequest.php`

**Validation Rules:**
```php
[
    'notes' => ['nullable', 'string', 'max:1000'],
]
```

**Authorization:**
- Use `authorize()` method to check if user owns the trip

---

#### ListTripsRequest
**File:** `app/Http/Requests/Trip/ListTripsRequest.php`

**Validation Rules:**
```php
[
    'page' => ['nullable', 'integer', 'min:1'],
    'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
    'user_id' => ['nullable', 'integer', 'exists:users,id'],
    'status' => ['nullable', 'string', 'in:in_progress,completed,auto_stopped'],
    'date_from' => ['nullable', 'date'],
    'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
]
```

**Authorization:**
- Employees cannot filter by `user_id` (ignored if provided)
- Only supervisor/admin can use `user_id` filter

---

### 3. Create TripController

**File:** `app/Http/Controllers/TripController.php`

**Methods:**

#### index(ListTripsRequest $request)
- Apply authorization via policy (`authorize('viewAny', Trip::class)`)
- Build query with filters (date_from, date_to, status, user_id)
- For employees: `->where('user_id', auth()->id())`
- For supervisor/admin: optional `user_id` filter
- Eager load user relationship: `->with('user:id,name,email')`
- Paginate: `->paginate($request->input('per_page', 20))`
- Return JSON with trip list and meta

#### store(StartTripRequest $request)
- Authorize via policy: `$this->authorize('create', Trip::class)`
- Check for existing active trip:
  ```php
  $activeTrip = Trip::where('user_id', auth()->id())
      ->where('status', TripStatus::InProgress)
      ->exists();
  
  if ($activeTrip) {
      return response()->json([
          'message' => 'You already have an active trip'
      ], 422);
  }
  ```
- Call `TripService::startTrip(auth()->user(), $request->input('notes'))`
- Return JSON with trip data (201 Created)

#### show(Trip $trip)
- Authorize via policy: `$this->authorize('view', $trip)`
- Eager load relationships: `$trip->load(['user:id,name,email', 'speedLogs'])`
- Return JSON with trip details including speed logs

#### update(EndTripRequest $request, Trip $trip)
- Authorize via policy: `$this->authorize('update', $trip)`
- Validate trip is in progress (throw 422 if not)
- Call `TripService::endTrip($trip)`
- If notes provided, update: `$trip->update(['notes' => $request->input('notes')])`
- Return JSON with updated trip data

**Controller Structure:**
```php
namespace App\Http\Controllers;

use App\Http\Requests\Trip\EndTripRequest;
use App\Http\Requests\Trip\ListTripsRequest;
use App\Http\Requests\Trip\StartTripRequest;
use App\Models\Trip;
use App\Services\TripService;
use Illuminate\Http\JsonResponse;

class TripController extends Controller
{
    public function __construct(private TripService $tripService) {}
    
    public function index(ListTripsRequest $request): JsonResponse
    public function store(StartTripRequest $request): JsonResponse
    public function show(Trip $trip): JsonResponse
    public function update(EndTripRequest $request, Trip $trip): JsonResponse
}
```

---

### 4. Register API Routes

**File:** `routes/api.php`

Add inside `auth:sanctum` middleware group:

```php
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes (existing)
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');
    
    // Trip routes (new)
    Route::get('/trips', [TripController::class, 'index'])->name('trips.index');
    Route::post('/trips', [TripController::class, 'store'])->name('trips.store');
    Route::get('/trips/{trip}', [TripController::class, 'show'])->name('trips.show');
    Route::put('/trips/{trip}', [TripController::class, 'update'])->name('trips.update');
});
```

---

### 5. Create Feature Tests

**File:** `tests/Feature/Trips/TripControllerTest.php`

**Test Coverage (minimum 20 tests):**

**Start Trip (POST /api/trips):**
- `test_employee_can_start_trip()`
- `test_employee_cannot_start_trip_when_one_is_already_active()`
- `test_unauthenticated_user_cannot_start_trip()`
- `test_start_trip_with_notes()`
- `test_start_trip_validates_notes_max_length()`
- `test_supervisor_can_start_trip()`

**End Trip (PUT /api/trips/{id}):**
- `test_employee_can_end_own_trip()`
- `test_employee_cannot_end_another_users_trip()`
- `test_cannot_end_trip_that_is_not_in_progress()`
- `test_ending_trip_calculates_statistics()`
- `test_end_trip_with_notes_updates_notes_field()`
- `test_unauthenticated_user_cannot_end_trip()`

**List Trips (GET /api/trips):**
- `test_employee_can_list_own_trips()`
- `test_employee_cannot_see_other_users_trips()`
- `test_supervisor_can_list_all_trips()`
- `test_supervisor_can_filter_trips_by_user_id()`
- `test_list_trips_with_pagination()`
- `test_list_trips_filtered_by_status()`
- `test_list_trips_filtered_by_date_range()`
- `test_list_trips_validates_per_page_max_100()`
- `test_list_trips_eager_loads_user_relationship()`

**Show Trip (GET /api/trips/{id}):**
- `test_employee_can_view_own_trip_details()`
- `test_employee_cannot_view_another_users_trip()`
- `test_supervisor_can_view_any_trip()`
- `test_trip_details_includes_speed_logs()`
- `test_trip_details_includes_user_info()`
- `test_unauthenticated_user_cannot_view_trip()`

**Test Helpers:**
```php
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripControllerTest extends TestCase
{
    use RefreshDatabase;
    
    private function actingAsEmployee(): User
    {
        return User::factory()->employee()->create();
    }
    
    private function actingAsSupervisor(): User
    {
        return User::factory()->supervisor()->create();
    }
}
```

---

## Key Technical Considerations

### Authorization Strategy

Use Laravel Policy pattern consistently:
```php
// In controller
$this->authorize('view', $trip);

// In policy
public function view(User $user, Trip $trip): bool
{
    return $user->id === $trip->user_id 
        || $user->isSupervisor() 
        || $user->isAdmin();
}
```

### Query Performance

**Eager Loading:**
- Always eager load `user` relationship for list and show endpoints
- For show endpoint, eager load `speedLogs` relationship
- Use `->with('user:id,name,email')` to only select needed columns

**Indexes:**
Database already has indexes on:
- `[user_id, started_at]` - for efficient user trip queries
- `status` - for status filtering
- `ended_at` - for date range queries

### Error Handling

**Common HTTP Status Codes:**
- 200: Success (list, show, update)
- 201: Created (store)
- 401: Unauthenticated
- 403: Forbidden (policy denial)
- 404: Trip not found
- 422: Validation error / business rule violation

**Business Rule Errors:**
- Active trip already exists → 422
- Trip not in progress → 422

---

## Definition of Done Checklist

- [ ] TripPolicy created with all authorization methods
- [ ] Policy registered in service provider
- [ ] StartTripRequest, EndTripRequest, ListTripsRequest created
- [ ] TripController with 4 methods implemented
- [ ] API routes registered in routes/api.php
- [ ] All 26+ feature tests passing
- [ ] Code formatted with Pint (no linter errors)
- [ ] PHPDoc on all public methods
- [ ] Eager loading optimized
- [ ] Manual API testing with Postman/Insomnia (optional but recommended)

---

## Implementation Order

1. **Create TripPolicy** → Authorization foundation
2. **Create Form Requests** → Validation rules
3. **Create TripController** → Business logic wiring
4. **Register Routes** → Expose endpoints
5. **Create Feature Tests** → Comprehensive coverage
6. **Run Tests & Lint** → Verify quality

---

## Testing Commands

```bash
# Run specific test file
php artisan test tests/Feature/Trips/TripControllerTest.php --compact

# Run all tests
php artisan test --compact

# Format code
vendor/bin/pint --dirty --format agent
```

---

## Manual Testing Script (Optional)

After implementation, test with these curl commands:

```bash
# 1. Start trip
curl -X POST http://localhost/api/trips \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"notes": "Morning commute"}'

# 2. List trips
curl -X GET "http://localhost/api/trips?per_page=10&status=completed" \
  -H "Authorization: Bearer {token}"

# 3. Show trip
curl -X GET http://localhost/api/trips/1 \
  -H "Authorization: Bearer {token}"

# 4. End trip
curl -X PUT http://localhost/api/trips/1 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"notes": "Trip completed safely"}'
```
