# Speed Tracker Application - Scrum Workflow

## Project Overview

**Project Name:** TBD (LeadFoot, VeloCheck, etc.)  
**Project Type:** Speed tracking and monitoring system  
**Team Size:** 1-2 developers (adjust as needed)  
**Sprint Duration:** 1 week  
**Total Sprints:** 8  
**Target Launch:** 8 weeks from start

---

## Roles & Responsibilities

**Product Owner:** You (Zulfikar)  
**Scrum Master:** You or designated  
**Development Team:** Developers assigned  
**Stakeholders:** Supervisors who will use the dashboard

---

## Product Backlog

### Epic 1: Authentication & User Management
- User registration (admin only)
- User login/logout
- Role-based access control (employee, supervisor, admin)
- Profile management

### Epic 2: Speed Tracking (Employee)
- GPS speed detection
- Real-time speedometer display
- Start/stop trip manually
- Auto-stop after 30 min of no movement
- Speed logging every 5 seconds
- Trip statistics (distance, max speed, average)
- Speed limit violation detection

### Epic 3: Offline Functionality
- IndexedDB storage for offline trips
- Service Worker implementation
- Background sync when online
- Sync status indicators
- PWA installation support

### Epic 4: Trip Management
- View trip history
- Filter trips by date range
- Trip detail view with speed logs
- Delete trips (admin/supervisor only)
- Trip notes/comments

### Epic 5: Dashboard (Supervisor/Admin)
- Overview statistics
- Active trips monitoring
- All employee trips view
- Violation leaderboard
- Export reports (CSV)

### Epic 6: Settings & Configuration
- Speed limit configuration (global)
- Auto-stop duration setting
- Speed log interval setting
- User management (CRUD)

### Epic 7: Deployment & DevOps
- Production environment setup
- Database migration
- SSL configuration
- PWA manifest setup
- Performance optimization

### Epic 8: Testing & Documentation
- API testing
- Frontend unit tests
- E2E testing critical flows
- User documentation
- Admin guide

---

## Sprint Planning

## Sprint 1: Project Setup & Authentication (Week 1)

### Sprint Goal
Set up development environment and implement user authentication system.

### User Stories

#### US-1.1: Initialize Laravel 12 Project
**As a** developer  
**I want** to set up Laravel 12 project with necessary dependencies  
**So that** I can start building the API

**Acceptance Criteria:**
- [ ] Laravel 12 installed via Composer
- [ ] Database configured (MySQL)
- [ ] Environment variables set up (.env)
- [ ] Basic folder structure following service pattern
- [ ] Git repository initialized

**Story Points:** 2  
**Priority:** Critical

---

#### US-1.2: Initialize Vue 3 PWA Project
**As a** developer  
**I want** to set up Vue 3 project with PWA support  
**So that** I can build the offline-capable frontend

**Acceptance Criteria:**
- [ ] Vue 3 project created with Vite
- [ ] Vue Router installed
- [ ] Pinia store installed
- [ ] PWA plugin configured
- [ ] Tailwind CSS (or preferred CSS framework) installed
- [ ] Axios configured for API calls

**Story Points:** 2  
**Priority:** Critical

---

#### US-1.3: Create Database Schema
**As a** developer  
**I want** to create database migrations  
**So that** the database structure is defined

**Acceptance Criteria:**
- [ ] Users table migration created
- [ ] Trips table migration created
- [ ] Speed logs table migration created
- [ ] Settings table migration created
- [ ] Foreign key relationships defined
- [ ] Indexes created on necessary columns
- [ ] Migrations tested (up/down)

**Story Points:** 3  
**Priority:** Critical

---

#### US-1.4: Implement User Authentication (Backend)
**As a** user  
**I want** to log in to the system  
**So that** I can access my account

**Acceptance Criteria:**
- [ ] Laravel Sanctum configured
- [ ] Login API endpoint created (`POST /api/auth/login`)
- [ ] Logout API endpoint created (`POST /api/auth/logout`)
- [ ] Get current user endpoint (`GET /api/auth/me`)
- [ ] Form request validation for login
- [ ] AuthService created (service pattern)
- [ ] Returns proper JWT token
- [ ] Returns user with role

**Story Points:** 5  
**Priority:** Critical

---

#### US-1.5: Implement Login Page (Frontend) ✅ COMPLETED
**As a** user  
**I want** a login page  
**So that** I can authenticate to the app

**Acceptance Criteria:**
- [x] Login.vue component created with email and password inputs
- [x] Client-side form validation (email format, password length)
- [x] Submit using Wayfinder + Inertia useForm composable
- [x] User data and token stored in localStorage via Pinia store
- [x] Role-based redirect (employee/supervisor/admin dashboards)
- [x] Backend error messages displayed (Laravel validation)
- [x] Loading state with disabled inputs during submission
- [x] Test with all three user roles (employee, supervisor, admin)

**Implementation Details:**
**Backend (`app/Http/Controllers/Auth/AuthController.php`):**
- Login method returns Inertia response with user + token as props
- Logout method supports both Inertia (redirect) and API (JSON) responses
- Fixed 500 error: changed from `Inertia::location()` to `redirect()`
- Full PHPDoc documentation with @param/@return/@throws

**Frontend - Login (`resources/js/pages/auth/Login.vue`):**
- Uses Inertia v3's `useForm` composable (NOT `useHttp`)
- Wayfinder integration: `form.submit(loginAction())` with route object
- Client-side validation before submission
- onSuccess callback stores user + token in Pinia auth store
- Role-based redirect after successful login
- Comprehensive JSDoc documentation

**Frontend - Auth Composable (`resources/js/composables/useAuth.ts`):**
- `handleLogout()` function using Inertia router + Wayfinder
- Uses `router.post(logoutAction(), {})` pattern
- Full JSDoc with @returns and @example

**Frontend - Auth Store (`resources/js/stores/auth.ts`):**
- User data persisted to localStorage (not just token)
- `initializeAuth()` restores both user and token on app startup
- Called in `app.ts` during Inertia app initialization
- Comprehensive documentation with JSDoc on all methods

**Frontend - Types (`resources/js/types/api.ts`):**
- TypeScript interfaces: LoginCredentials, LoginResponse, ApiError
- Full JSDoc on all type definitions

**Dashboard Pages:**
- Employee: `resources/js/pages/employee/Dashboard.vue`
- Supervisor: `resources/js/pages/supervisor/Dashboard.vue`
- Admin: `resources/js/pages/admin/Dashboard.vue`
- All display user data from auth store with logout button

**Key Architecture Decisions:**
1. **Wayfinder + useForm (NOT useHttp):**
   - `useForm` is the recommended Inertia v3 pattern for forms
   - `form.submit(wayfinderObject)` passes route object directly
   - Backend returns Inertia response, not JSON
2. **localStorage Persistence:**
   - Both user object and token persisted
   - Session survives page refresh
   - Initialized on app startup in `app.ts`
3. **Full Documentation:**
   - PHPDoc on all public methods
   - JSDoc on composables, stores, and types
   - Inline WHY comments for complex logic
   - Section comments in large templates

**Testing Results:**
- ✅ Login with employee/supervisor/admin accounts successful
- ✅ User data displays correctly on dashboard
- ✅ Page refresh preserves session (localStorage working)
- ✅ Logout redirects to login without 500 error
- ✅ No console errors during full flow
- ✅ All linters passing (PHP Pint, ESLint)

**Story Points:** 3  
**Priority:** Critical  
**Status:** ✅ Completed (March 30, 2026)

**Lessons Learned:**
- Wayfinder works differently with `useForm` vs `useHttp` vs `router`
- `useForm.submit()` expects route object: `loginAction()`
- `useHttp` expects URL string: `loginAction.url()`
- `router.visit()` accepts both route objects and URL strings
- Always check Wayfinder's `.form()` method availability (needs `--with-form` flag)

---

#### US-1.6: Implement Auth Store (Pinia) ✅ COMPLETED (Enhanced in US-1.5)
**As a** developer  
**I want** centralized authentication state  
**So that** auth data is accessible throughout the app

**Acceptance Criteria:**
- [x] Auth store created (`stores/auth.ts`)
- [x] State: user, token, isAuthenticated, role
- [x] Actions: login, logout, setUser, setToken
- [x] Getters: isEmployee, isSupervisor, isAdmin
- [x] Token persisted to localStorage
- [x] User data persisted to localStorage (added in US-1.5)
- [x] Auto-restore session on app init (initializeAuth method)

**Implementation Details:**
- Initially implemented in `resources/js/stores/auth.ts`
- Enhanced during US-1.5 to persist user data (not just token)
- TypeScript with proper type definitions
- Computed getters for role-based checks
- localStorage integration for both token AND user object
- `initializeAuth()` called in `app.ts` during bootstrap
- Comprehensive JSDoc documentation on all methods
- Export User interface and UserRole type for app-wide use

**Story Points:** 3  
**Priority:** Critical  
**Status:** ✅ Completed (Initial in US-1.2/1.3, Enhanced in US-1.5)

---

### Sprint 1 Tasks Breakdown

**Day 1-2:**
- US-1.1: Initialize Laravel 12 project
- US-1.2: Initialize Vue 3 PWA project
- US-1.3: Create database schema

**Day 3-4:**
- US-1.4: Implement authentication API
- US-1.6: Implement auth store

**Day 5:**
- US-1.5: Implement login page
- Testing & integration

**Sprint 1 Total Story Points:** 18

---

## Sprint 2: Core Trip Management (Backend) (Week 2)

### Sprint Goal
Build API endpoints for trip creation, speed logging, and trip management.

### User Stories

#### US-2.1: Create Trip Model & Service
**As a** developer  
**I want** Trip model with business logic  
**So that** trip data can be managed

**Acceptance Criteria:**
- [ ] Trip model created with relationships
- [ ] TripService created (service pattern)
- [ ] Methods: startTrip, endTrip, updateTripStats
- [ ] Calculates max speed, average speed, distance
- [ ] Violation count calculated
- [ ] Duration calculated

**Story Points:** 5  
**Priority:** Critical

---

#### US-2.2: Create Speed Log Model
**As a** developer  
**I want** SpeedLog model  
**So that** speed data can be stored

**Acceptance Criteria:**
- [ ] SpeedLog model created
- [ ] Belongs to Trip relationship
- [ ] is_violation flag calculated based on speed_limit
- [ ] Bulk insert method for efficiency

**Story Points:** 3  
**Priority:** Critical

---

#### US-2.3: Trip API Endpoints
**As a** employee  
**I want** to start and end trips via API  
**So that** my trips are recorded

**Acceptance Criteria:**
- [ ] `POST /api/trips` - Start new trip
- [ ] `PUT /api/trips/{id}` - End trip
- [ ] `GET /api/trips` - List trips (paginated, filtered)
- [ ] `GET /api/trips/{id}` - Get trip details with speed logs
- [ ] TripController created
- [ ] Authorization policies applied
- [ ] Validation for all inputs
- [ ] Returns proper status codes

**Story Points:** 8  
**Priority:** Critical

---

#### US-2.4: Speed Log Bulk Insert API
**As a** employee  
**I want** to sync multiple speed logs at once  
**So that** offline data can be uploaded efficiently

**Acceptance Criteria:**
- [ ] `POST /api/trips/{id}/speed-logs` endpoint
- [ ] Accepts array of speed log objects
- [ ] Validates each log entry
- [ ] Bulk inserts to database
- [ ] Updates trip statistics after insert
- [ ] Returns success/failure summary

**Story Points:** 5  
**Priority:** Critical

---

#### US-2.5: Settings API
**As a** admin  
**I want** to manage app settings via API  
**So that** configurations can be changed

**Acceptance Criteria:**
- [ ] Settings model/migration created
- [ ] `GET /api/settings` - Get all settings
- [ ] `PUT /api/settings` - Update settings (bulk)
- [ ] Default settings seeded (speed_limit, auto_stop_duration)
- [ ] SettingsService created
- [ ] Only admin can update settings

**Story Points:** 3  
**Priority:** High

---

#### US-2.6: Database Seeders
**As a** developer  
**I want** test data seeded  
**So that** I can test the application

**Acceptance Criteria:**
- [ ] UserSeeder: 1 admin, 2 supervisors, 10 employees
- [ ] SettingsSeeder: default settings
- [ ] TripSeeder: sample trips with speed logs (optional)
- [ ] All seeders run without errors

**Story Points:** 2  
**Priority:** Medium

---

### Sprint 2 Tasks Breakdown

**Day 1-2:**
- US-2.1: Create Trip model & service
- US-2.2: Create SpeedLog model

**Day 3-4:**
- US-2.3: Trip API endpoints
- US-2.4: Speed log bulk insert

**Day 5:**
- US-2.5: Settings API
- US-2.6: Database seeders
- Testing & API documentation

**Sprint 2 Total Story Points:** 26

---

## Sprint 3: Speedometer UI (Frontend) (Week 3)

### Sprint Goal
Build the core speedometer interface with GPS tracking and trip controls.

### User Stories

#### US-3.1: Geolocation Composable ✅ COMPLETED
**As a** developer  
**I want** a reusable geolocation composable  
**So that** GPS tracking logic is centralized

**Acceptance Criteria:**
- [x] `useGeolocation.ts` composable created
- [x] getCurrentSpeed() method (km/h)
- [x] watchSpeed() method (continuous tracking)
- [x] stopTracking() method
- [x] Handles permission requests
- [x] Error handling for denied permissions
- [x] Falls back gracefully if GPS unavailable

**Implementation Details:**
**File:** `resources/js/composables/useGeolocation.ts` (469 lines)
- Wraps VueUse geolocation with speed-specific features
- Converts m/s to km/h automatically
- Permission request with user-friendly Indonesian error messages
- Reactive state management with Vue Composition API
- Type-safe with full TypeScript coverage

**Story Points:** 5  
**Priority:** Critical  
**Status:** ✅ Completed (April 2, 2026)

---

#### US-3.2: Trip Store (Pinia) ✅ COMPLETED
**As a** developer  
**I want** centralized trip state management  
**So that** trip data is accessible across components

**Acceptance Criteria:**
- [x] Trip store created (`stores/trip.ts`)
- [x] State: currentTrip, speedLogs, isTracking, stats
- [x] Actions: startTrip, endTrip, addSpeedLog, updateStats (+ syncSpeedLogs, clearTrip)
- [x] Integrates with API service (Wayfinder + useHttp)
- [x] Calculates real-time stats (max, avg, distance)
- [x] Computed properties: hasActiveTrip, pendingLogCount, needsSync
- [x] Error handling with retry logic (exponential backoff)
- [x] Comprehensive JSDoc documentation

**Implementation Details:**

**Backend Integration:**
- Uses Wayfinder `TripController` for type-safe route generation
- Integrates with Inertia v3 `useHttp` for API requests
- Full TypeScript types matching Laravel backend models

**Files Created:**
- `resources/js/types/trip.ts` (277 lines) - TypeScript type definitions
- `resources/js/stores/trip.ts` (616 lines) - Pinia store with full documentation
- Updated: `resources/js/types/index.ts` (added trip exports)

**State Management:**
```typescript
{
  currentTrip: Trip | null,
  speedLogs: SpeedLog[],      // Buffer (sync every 10 logs)
  isTracking: boolean,
  stats: TripStats,           // Real-time calculations
  isStarting/isEnding/isSyncing: boolean,
  lastSyncAt: Date | null,
  error: string | null
}
```

**Key Actions:**
1. `startTrip(notes?)` - Creates trip via POST `/api/trips`
2. `addSpeedLog(speed, timestamp)` - Buffers speed with violation detection
3. `syncSpeedLogs()` - Bulk insert via POST `/api/trips/{id}/speed-logs` with retry logic
4. `endTrip(notes?)` - Completes trip via PUT `/api/trips/{id}`
5. `clearTrip()` - Resets state for next session

**Key Features:**
- **Speed Log Batching**: ~90% fewer API calls by syncing every 10 logs (50 seconds)
- **Real-time Statistics**: Local calculation for immediate user feedback
- **Retry Logic**: Exponential backoff (1s, 2s, 4s) with max 3 attempts
- **Violation Detection**: Automatic comparison against settings.speed_limit
- **Type Safety**: Full TypeScript coverage with backend model matching

**Integration Points:**
- Uses `useSettingsStore` for speed_limit configuration
- Integrates with `useGeolocation` composable (US-3.1)
- Provides data for Speedometer components (US-3.3, 3.4, 3.5)

**Testing:**
- ESLint passing (exit code 0)
- Build successful (219.39 kB bundle)
- Manual testing checklist created
- See: `docs/US-3.2_IMPLEMENTATION_SUMMARY.md`

**Story Points:** 5  
**Priority:** Critical  
**Status:** ✅ Completed (April 2, 2026)

**Lessons Learned:**
- Inertia v3's `useHttp` preferred over Axios for API consistency
- Speed log batching significantly improves API efficiency
- Local statistics calculation provides better UX than waiting for backend
- TypeScript types matching backend models prevented many potential bugs

---

#### US-3.3: Speedometer Gauge Component ✅ COMPLETED
**As a** employee  
**I want** a visual speedometer gauge  
**So that** I can see my current speed clearly

**Acceptance Criteria:**
- [x] SpeedGauge.vue component created
- [x] Circular gauge design (270° SVG arc like car speedometer)
- [x] Shows current speed (large text) and unit label
- [x] Speed limit marker on gauge (circle with tick mark)
- [x] Color coding (green: safe, yellow: near limit, red: violation)
- [x] Smooth animation for speed changes (500ms transitions)
- [x] Responsive design (sm/md/lg variants, mobile-optimized)
- [x] Full accessibility support (ARIA labels, screen reader text)
- [x] TypeScript type definitions created
- [x] Test/demo page with interactive controls

**Implementation Details:**

**Component (`resources/js/components/speedometer/SpeedGauge.vue`):**
- 270° circular SVG gauge using polar to cartesian coordinate conversion
- Dynamic arc path generation based on current speed percentage
- Three color zones: green (0-85% limit), yellow (85-100% limit), red (>100% limit)
- Speed limit marker positioned dynamically on arc
- Central speed display with large font (6xl-8xl) and unit label
- Responsive size variants: sm (192px), md (256-320px), lg (320-384px)
- Smooth CSS transitions for arc, color, and text changes
- Integration with settings store for dynamic speed_limit configuration
- Full ARIA accessibility with live regions and screen reader fallback

**Types (`resources/js/types/speedometer.ts`):**
- SpeedGaugeProps interface with speed, speedLimit, maxSpeed, size
- GaugeColors, ArcCoordinates, SpeedZones, GaugeAnimationConfig interfaces
- Comprehensive JSDoc documentation on all types

**Demo Page (`resources/js/pages/test/SpeedGaugeDemo.vue`):**
- Interactive speed slider and preset buttons (0, 30, 55, 75 km/h)
- Auto-increment feature for continuous animation testing
- Configuration controls (speed limit, max speed, size selection)
- Real-time status badge showing current zone (safe/warning/violation)
- Testing checklist included in UI

**Key Technical Decisions:**
1. **SVG over Canvas:** Resolution-independent, better accessibility, easier styling
2. **270° Arc:** Better visibility than full circle, leaves space for speed text
3. **Color Thresholds:** 85% threshold for yellow provides advance warning
4. **Smooth Transitions:** 500ms ease-in-out prevents jarring visual changes
5. **Settings Store Integration:** Dynamic speed_limit allows admin configuration
6. **Type Safety:** Full TypeScript coverage prevents prop misuse

**Code Quality:**
- ✅ ESLint passing (0 errors)
- ✅ Prettier formatted
- ✅ TypeScript type-safe (no errors in new files)
- ✅ Comprehensive HTML comments and inline documentation

**Story Points:** 8  
**Priority:** Critical  
**Status:** ✅ Completed (April 2, 2026)

**Integration Ready For:**
- US-3.4: Trip Controls Component
- US-3.5: Trip Stats Display
- US-3.6: Speedometer Page Integration

**Usage Example:**
```vue
<script setup lang="ts">
import { useGeolocation } from '@/composables/useGeolocation';
import SpeedGauge from '@/components/speedometer/SpeedGauge.vue';

const { speedKmh } = useGeolocation();
</script>

<template>
  <SpeedGauge :speed="speedKmh" :speed-limit="60" size="lg" />
</template>
```

---

#### US-3.4: Trip Controls Component ✅ COMPLETED
**As a** employee  
**I want** start/stop trip buttons  
**So that** I can control trip recording

**Acceptance Criteria:**
- [x] TripControls.vue component created
- [x] Start Trip button (large, prominent)
- [x] Stop Trip button (appears when trip active)
- [x] Disabled states during API calls
- [x] Confirmation dialog before stopping trip
- [x] Shows trip duration (real-time countdown)

**Implementation Details:**
**Component (`resources/js/components/speedometer/TripControls.vue`):**
- Large, touch-friendly start/stop buttons with loading states
- GPS permission request before starting trip
- Real-time duration display (HH:MM:SS format, updates every second)
- Inline confirmation dialog with backdrop and Yes/No buttons
- Automatic speed logging via geolocation composable
- Auto-sync speed logs every 10 logs (50 seconds) with retry logic
- Error messages for GPS permission denied and API failures
- Mobile-first responsive design with Tailwind CSS
- Comprehensive JSDoc and HTML documentation

**Demo Page (`resources/js/pages/test/TripControlsDemo.vue`):**
- Interactive testing page with SpeedGauge integration
- Real-time status monitoring (trip, GPS, permission)
- Trip statistics display during active trip
- Debug information panel with store state
- Testing checklist with instructions
- Access via `/test` → "TripControls Demo" card

**Key Features:**
1. **Trip Lifecycle:** Permission → Start API → GPS tracking → Speed logging → Stop → Sync → End API
2. **Duration Display:** Local calculation from trip start time, updates every second
3. **Speed Logging:** Buffered locally, synced in batches (10 logs/50s) to reduce API calls by ~90%
4. **Error Handling:** GPS permission, network failures, API errors with user-friendly Indonesian messages
5. **Confirmation Dialog:** Built-in Vue Teleport modal (no external library)
6. **Resource Cleanup:** Intervals and GPS tracking properly cleaned up on unmount

**Testing Results:**
- ✅ ESLint passing (exit code 0)
- ✅ TypeScript compilation successful
- ✅ Build successful (272 KB, gzip: 86 KB)
- ✅ All acceptance criteria met
- ✅ Demo page functional with real GPS integration

**Story Points:** 3  
**Priority:** Critical  
**Status:** ✅ Completed (April 2, 2026)

**Lessons Learned:**
- Local duration calculation needed for smooth real-time display
- Speed log batching significantly reduces API calls and improves battery life
- Inline confirmation dialog sufficient without external modal library
- Combined loading state (`isLoading`) simplifies button state management

---

#### US-3.5: Trip Stats Display ✅ COMPLETED
**As a** employee  
**I want** to see real-time trip statistics  
**So that** I know my trip metrics

**Acceptance Criteria:**
- [x] TripStats.vue component created with card-based layout
- [x] Shows: max speed, average speed, distance, duration, violations
- [x] Updates in real-time during trip (reactive to trip store)
- [x] Violation count displayed with color-coded badge (green/red)
- [x] Clean, card-based layout with shadow and rounded corners
- [x] Icons for each metric (emojis)
- [x] Compact mode support via props
- [x] Empty state when no trip active
- [x] Smooth CSS transitions for value changes
- [x] TypeScript types defined (TripStatsProps, FormattedTripStats)
- [x] Comprehensive JSDoc documentation
- [x] Demo page created (TripStatsDemo.vue)
- [x] Full integration test with TripControls + SpeedGauge
- [x] ESLint passing, build successful

**Implementation Details:**

**Component (`resources/js/components/speedometer/TripStats.vue`):**
- Reactive updates from trip store stats (currentSpeed, maxSpeed, averageSpeed, distance, duration, violationCount)
- Grid layout: 3 columns for speed metrics, 2 columns for distance/duration
- Formatting utilities: formatSpeed (1 decimal), formatDistance (2 decimals), formatDuration (HH:MM:SS or MM:SS)
- Color-coded sections: blue (current), red (max), green (average), purple (distance), orange (duration)
- Violation badge: green for 0 violations, red for >0 with warning message
- Compact mode reduces padding and text sizes for flexible layouts
- Empty state message when no active trip
- Full ARIA accessibility labels for screen readers
- Comprehensive JSDoc with usage examples

**Types (`resources/js/types/speedometer.ts`):**
- TripStatsProps interface: compact mode configuration
- FormattedTripStats interface: pre-formatted display strings
- Full JSDoc documentation on all interfaces

**Demo Page (`resources/js/pages/test/TripStatsDemo.vue`):**
- Component preview with mode toggles (normal/compact)
- Mock data controls with sliders for all 6 metrics
- Preset buttons: Simulate Trip, Reset, Zero Values, Edge Cases
- Full integration test section with TripControls + SpeedGauge
- Interactive testing checklist (10 items)
- Debug information panel showing raw store values
- Real-time updates demonstration

**Testing Results:**
- ✅ ESLint passing (exit code 0)
- ✅ Build successful (290.12 kB bundle, gzip: 89.02 kB)
- ✅ PHP Pint passing
- ✅ All acceptance criteria met
- ✅ Demo page functional at /test/trip-stats-demo

**Key Features:**
- Real-time reactivity (no polling needed)
- Proper formatting with consistent precision
- Visual feedback with color coding and transitions
- Flexible sizing with compact mode
- Full accessibility support
- Performance optimized with computed properties

**Story Points:** 3  
**Priority:** High  
**Status:** ✅ Completed (April 2, 2026)

**Integration Ready:**
- Ready for US-3.6 (Speedometer Page Integration)
- Component can be imported and used immediately
- All dependencies satisfied (Trip Store, Settings Store)
- Demo page available for testing and reference

---

#### US-3.6: Speedometer Page Integration ✅ COMPLETED
**As a** employee  
**I want** a complete speedometer page  
**So that** I can track my trips

**Acceptance Criteria:**
- [x] Speedometer.vue view created with production design
- [x] Canvas gauge with tick marks and speed labels
- [x] VeloTrack branding (Bebas Neue typography)
- [x] Speed limit controls (+/- buttons) on page
- [x] Unit toggle (km/h ↔ mph)
- [x] GPS accuracy bar with color coding
- [x] Satellites count (estimated from GPS accuracy)
- [x] Haversine distance calculation
- [x] Integrates TripControls component (backend API)
- [x] Integrates with Trip Store (POST /api/trips, sync logs, end trip)
- [x] Integrates with Settings Store (GET /api/settings)
- [x] Professional dark theme with glow effects
- [x] Layout optimized for mobile (portrait)
- [x] Speed logging every 5 seconds via Trip Store
- [x] Route guard: employee only (auth + role middleware)
- [x] Navigation link added to dashboard (page accessibility)

**Implementation Details:**

**Backend:**
- Created `CheckRole` middleware for role-based access control
- Registered middleware as `'role'` alias in `bootstrap/app.php`
- Organized routes by role with proper middleware groups
- Route: `/employee/speedometer` with `auth` and `role:employee` middleware
- API integration: TripController (POST /api/trips, POST /api/trips/{id}/speed-logs, PUT /api/trips/{id})
- API integration: SettingsController (GET /api/settings)

**Frontend - Production Speedometer Page (`resources/js/pages/employee/Speedometer.vue`):**
- **VeloTrack branding** with Bebas Neue typography and cyan glow effects
- **Canvas gauge** (ProductionGauge component) with tick marks, speed labels, and gradient arcs
- **Speed limit banner** with +/- controls (adjustable 10-200 range)
- **Unit toggle** (km/h ↔ mph) with full conversion across all displays
- **GPS accuracy bar** with color-coded percentage (cyan/yellow/red zones)
- **Satellites count** estimated from GPS accuracy (4-16 satellites)
- **Trip bar** displaying distance (Haversine formula), duration, satellites
- **Stats grid** with max/avg speed (Share Tech Mono font)
- **Dark theme** (#0a0c0f background) with noise overlay texture
- **Professional design system** matching HTML specification
- Integrates with `useTripStore()` for backend API calls
- Integrates with `useSettingsStore()` for speed limit configuration
- Integrates with `useGeolocation()` for real-time GPS tracking

**Frontend - Components:**
- `ProductionGauge.vue`: Canvas-based gauge with 60fps rendering, 10 tick marks, gradient fills, limit marker
- `TripControls.vue`: Start/stop buttons with GPS permission, API integration via Trip Store
- `TripStats.vue`: Real-time statistics display from Trip Store

**Frontend - Dashboard Navigation (`resources/js/pages/employee/Dashboard.vue`):**
- Added prominent "Start Speedometer" card with gradient styling
- Touch-friendly design (≥44px targets)
- Clear call-to-action and visual hierarchy

**Utility Functions Created:**
- `resources/js/utils/distance.ts`: Haversine distance calculation for accurate GPS tracking
- `resources/js/utils/units.ts`: Speed unit conversion (m/s ↔ km/h ↔ mph), satellite estimation

**Trip Store Integration:**
- `startTrip()` → POST /api/trips (creates trip in database)
- `addSpeedLog()` → buffers speed logs locally
- `syncSpeedLogs()` → POST /api/trips/{id}/speed-logs (bulk insert every 10 logs/50s)
- `endTrip()` → PUT /api/trips/{id} (marks trip complete, calculates final stats)
- Real-time stats calculation (maxSpeed, averageSpeed, distance, duration, violationCount)

**Key Features:**
- **Backend integrated**: All trip data saves to database via API
- **Haversine distance**: Accurate GPS-based distance calculation (not straight-line)
- **Speed log batching**: Reduces API calls by ~90% (syncs every 10 logs)
- **Unit conversion**: Dynamic switching between km/h and mph
- **Professional styling**: VeloTrack branding with Bebas Neue, Share Tech Mono, Barlow fonts
- **GPS monitoring**: Accuracy bar, satellite count, status indicator
- **Mobile-optimized**: Portrait layout, touch-friendly, responsive

**Code Quality:**
- ✅ Build successful: 247.78 kB (80.02 kB gzipped)
- ✅ ESLint passing (0 errors)
- ✅ PHP Pint formatted
- ✅ TypeScript compilation successful
- ✅ Full backend API integration verified
- ✅ Wayfinder routes regenerated

**Files Created:**
- `resources/js/components/speedometer/ProductionGauge.vue`
- `resources/js/utils/distance.ts` (Haversine formula)
- `resources/js/utils/units.ts` (unit conversion utilities)

**Files Modified:**
- `resources/js/pages/employee/Speedometer.vue` (rebuilt with HTML spec features)
- `resources/js/composables/useGeolocation.ts` (added speedMps, accuracy exports)
- `resources/js/pages/employee/Dashboard.vue` (navigation card)
- `bootstrap/app.php` (registered middleware)
- `routes/web.php` (added speedometer route)

**Testing:**
- ✅ Build verification: successful
- ✅ Route registration: confirmed via `php artisan route:list`
- ✅ API integration: Trip Store calls backend endpoints
- ✅ Linting: ESLint and PHP Pint passing
- ✅ Store integration: Settings fetched from backend, trips saved to database

**Story Points:** 5  
**Priority:** Critical  
**Status:** ✅ Completed (April 2, 2026)

**Lessons Learned:**
- HTML spec features (canvas gauge, speed limit controls, unit toggle) enhance UX significantly
- Trip Store provides clean abstraction for backend API integration
- Haversine distance calculation essential for accurate GPS tracking
- Professional typography and dark theme match production requirements
- Speed log batching (bulk insert) critical for performance and battery life

---

#### US-3.7: Speed Limit Violation Alert ✅ COMPLETED
**As a** employee  
**I want** to be alerted when I exceed speed limit  
**So that** I can slow down

**Acceptance Criteria:**
- [x] Browser notification when speed > limit
- [x] Audio alert (beep sound)
- [x] Visual indicator on speedometer (red flash)
- [x] Alert only once per violation (not continuous)
- [x] Can be toggled on/off in settings

**Implementation Details:**

**Backend (`database/seeders/SettingsSeeder.php`):**
- Added `violation_alerts_enabled` setting with default value `'true'`
- Setting description: "Enable/disable speed violation alerts (browser notification, audio, visual)"
- No migration needed (key-value settings table structure supports dynamic fields)

**Frontend - Violation Alert Composable (`resources/js/composables/useViolationAlert.ts`):**
- Centralized multi-channel alert logic (notification + audio + visual)
- Browser Notification API integration with permission handling
- Web Audio API beep generation (800Hz, 300ms, no audio files needed)
- Smart violation detection with 10-second cooldown period
- State management: `isViolating`, `lastViolationAt`, `notificationPermission`
- Graceful fallback if notification permission denied
- Full TypeScript types and comprehensive JSDoc documentation

**Frontend - SpeedGauge Enhancement (`resources/js/components/speedometer/SpeedGauge.vue`):**
- Added `isFlashing` reactive state for animation control
- Created `triggerFlash()` method exposed to parent via `defineExpose`
- Implemented CSS keyframes animation: red pulse with scale effect (500ms)
- Animation: opacity pulse (1→0.5→0.7→0.5→1) + scale (1→1.05→1.02→1.05→1)
- GPU-accelerated animation for 60fps performance

**Frontend - Speedometer Integration (`resources/js/pages/employee/Speedometer.vue`):**
- Imported `useViolationAlert` composable
- Added `speedGaugeRef` for component method calls
- Implemented violation detection watch on `[speedKmh, speedLimit]`
- Triggers all alert channels when violation detected (notification + beep + flash)
- Implemented permission request watch on `hasActiveTrip`
- Auto-requests notification permission when trip starts (if alerts enabled)
- Resets violation state when trip ends
- Added inline alert toggle button in header (bell icon)

**Frontend - Settings Store (`resources/js/stores/settings.ts`):**
- Added `violation_alerts_enabled: boolean` to `AppSettings` interface
- Default value: `true` (alerts enabled by default)
- Updated `reset()` method to include new field

**Alert Toggle UI (Speedometer Header):**
- Location: Right side of speedometer header
- Style: Green background when enabled, gray when disabled
- Icon: Bell icon (Heroicons) with status indicator
- Text: "Alerts On" / "Alerts Off" (hidden on mobile for space)
- Behavior: Toggles `violation_alerts_enabled` in settings store
- Accessibility: Full ARIA labels and title attributes

**Alert Flow:**
1. User exceeds speed limit
2. Watch detects violation via `checkViolation(speed, limit)`
3. If new violation (not already violating, cooldown expired):
   - `triggerAlert()` fires notification + beep
   - `speedGaugeRef.triggerFlash()` triggers visual flash
4. 10-second cooldown starts
5. Remain above limit → no additional alerts (prevent spam)
6. Drop below limit, exceed again after 10s → new alert fires

**Cooldown Logic:**
```
Time:    0s      5s      10s     11s     16s
Speed:   65      70      55      66      70
Limit:   60      60      60      60      60
Violate: YES     YES     NO      YES     YES
Alert:   FIRE    ❌      ❌      FIRE    ❌
```

**Browser Compatibility:**
- **Notifications**: Full support in Chrome, Firefox, Edge; Limited on iOS Safari
- **Web Audio API**: Full support in all modern browsers
- **CSS Animations**: 100% support across all browsers

**Key Technical Decisions:**
1. **Web Audio API over audio file**: No network request, lighter bundle (~3KB increase)
2. **10-second cooldown**: Balance between alert effectiveness and user annoyance
3. **Inline toggle over settings page**: Faster access, better UX for single setting
4. **Watch-based detection**: Reactive to speed changes, no polling needed
5. **Multi-channel redundancy**: Ensures user notices (notification + audio + visual)

**Testing Results:**
- ✅ Build successful: 298.74 KB bundle (91.64 KB gzipped)
- ✅ ESLint passing (0 errors)
- ✅ PHP Pint passing
- ✅ TypeScript compilation successful
- ✅ Settings seeder run successfully
- ✅ All acceptance criteria met
- 📋 Manual testing checklist documented (requires GPS-enabled device)

**Files Created:**
- `resources/js/composables/useViolationAlert.ts` (450 lines)
- `docs/US-3.7_IMPLEMENTATION_SUMMARY.md` (comprehensive documentation)

**Files Modified:**
- `database/seeders/SettingsSeeder.php` (added violation_alerts_enabled)
- `resources/js/stores/settings.ts` (added field to interface)
- `resources/js/components/speedometer/SpeedGauge.vue` (flash animation)
- `resources/js/pages/employee/Speedometer.vue` (integration + toggle UI)

**Story Points:** 3  
**Priority:** Medium  
**Status:** ✅ Completed (April 2, 2026)

**Lessons Learned:**
- Web Audio API provides excellent programmatic audio without file overhead
- Multi-channel alerts ensure user notices violations effectively
- Smart cooldown prevents alert fatigue while maintaining safety
- Inline toggle provides quick access without navigation overhead
- TypeScript types caught several potential bugs during development

---

### Sprint 3 Tasks Breakdown

**Day 1-2:**
- US-3.1: Geolocation composable
- US-3.2: Trip store

**Day 3-4:**
- US-3.3: Speedometer gauge component
- US-3.4: Trip controls component
- US-3.5: Trip stats display

**Day 5:**
- US-3.6: Speedometer page integration
- US-3.7: Speed limit violation alert
- Testing on mobile devices

**Sprint 3 Total Story Points:** 32

---

## Sprint 4: Trip History & Employee Views (Week 4)

### Sprint Goal
Build trip history, statistics, and profile pages for employees.

### User Stories

#### US-4.1: My Trips List Page ✅
**As a** employee  
**I want** to view my past trips  
**So that** I can review my driving history

**Acceptance Criteria:**
- [x] MyTrips.vue view created
- [x] Shows list of trips (paginated)
- [x] Displays: date, duration, distance, max speed, violations
- [x] Filter by date range
- [x] Sort by date (newest first)
- [x] Click trip to view details (placeholder ready for US-4.2)
- [x] Empty state when no trips

**Story Points:** 5  
**Priority:** High  
**Status:** ✅ **COMPLETED** (Sprint 4)  
**Completed:** April 2, 2026

**Implementation Notes:**
- Created 7 new frontend components (MyTrips.vue, TripCard, EmptyState, Pagination, TripListFilters, useTrips composable, date utils)
- Added `/employee/my-trips` route with auth + role:employee middleware
- Integrated navigation card in employee dashboard
- Fixed Sanctum stateful auth for SPA (added EnsureFrontendRequestsAreStateful middleware)
- Factory already exists with comprehensive states (completed, autoStopped, withViolations, synced)
- All components follow VeloTrack dark theme design system
- Responsive layout with mobile-first approach
- Empty state handles both "no trips" and "no filter results" scenarios
- Trip cards ready for click navigation to detail page (US-4.2)

---

#### US-4.2: Trip Detail Page
**As a** employee  
**I want** to view detailed trip information  
**So that** I can see full speed log and metrics

**Acceptance Criteria:**
- [ ] TripDetail.vue view created
- [ ] Shows all trip metadata
- [ ] Speed log chart (line chart over time)
- [ ] Violation markers on chart
- [ ] Summary statistics
- [ ] Back button to trip list

**Story Points:** 5  
**Priority:** High

---

#### US-4.3: My Statistics Page ✅ COMPLETED
**As a** employee  
**I want** to see my personal statistics  
**So that** I can track my performance

**Acceptance Criteria:**
- [x] MyStatistics.vue view created
- [x] Total trips count
- [x] Total distance traveled
- [x] Average speed (overall)
- [x] Violation count
- [x] Charts: trips over time, violations over time
- [x] Period selector (week, month, year)

**Implementation Details:**

**Backend - Service Layer (`app/Services/StatisticsService.php`):**
- Period-based aggregation logic (week/month/year)
- Summary calculations: total trips, distance, avg speed, violations
- Time-series data grouping (daily for week/month, monthly for year)
- Support for last 12 months maximum
- Carbon/CarbonImmutable compatibility with CarbonInterface type hint

**Backend - Controller (`app/Http/Controllers/Employee/StatisticsController.php`):**
- Dual endpoint approach: Inertia view + JSON API
- `GET /employee/statistics` - Inertia page render
- `GET /api/statistics/my-stats` - JSON API endpoint
- Period validation with default to 'month'
- Service pattern with dependency injection

**Frontend - Main Page (`resources/js/pages/employee/MyStatistics.vue`):**
- Integrated with EmployeeLayout for consistent navigation
- Period selector with server-side refetch via Inertia
- 4 summary statistics cards with color-coded variants
- Two Chart.js charts: bar (trips) and line (violations)
- Empty state with call-to-action for new users
- Reactive updates with Inertia `only` option

**Frontend - Components:**
- `StatCard.vue` - Reusable metric display with 5 color variants (blue, green, purple, red, orange)
- `PeriodSelector.vue` - Segmented control for week/month/year
- `TripsChart.vue` - Bar chart using Chart.js with VeloTrack dark theme
- `ViolationsChart.vue` - Line chart with gradient fill and tooltips

**Frontend - Types (`resources/js/types/statistics.ts`):**
- Complete TypeScript interfaces: UserStatistics, StatisticsSummary, ChartDataPoint, PeriodInfo
- Type-safe period selector: Period = 'week' | 'month' | 'year'
- Props interfaces for all components

**Routes:**
- Web route registered in `routes/web.php`: `employee.statistics`
- API route registered in `routes/api.php`: `statistics.my-stats`
- Wayfinder generation run for type-safe routing

**Design Decisions:**
1. **Chart Selection:**
   - Bar chart for trips (discrete counts per period)
   - Line chart for violations (trend visualization)
2. **Data Fetching:**
   - Server-side rendering with Inertia (no client-side API calls)
   - Period changes trigger partial reloads with `only` option
3. **Period Granularity:**
   - Week: Daily breakdown (7 days)
   - Month: Daily breakdown (up to 31 days)
   - Year: Monthly breakdown (12 months)
4. **VeloTrack Theme:**
   - Dark theme with cyan accent (#22d3ee)
   - Gradient backgrounds on stat cards
   - Chart.js custom configuration for dark mode

**Story Points:** 5  
**Priority:** Medium  
**Status:** ✅ Completed (April 2, 2026)

---

#### US-4.4: Profile Page ✅ COMPLETED
**As a** user  
**I want** to view and edit my profile  
**So that** I can update my information

**Acceptance Criteria:**
- [x] Profile.vue view created
- [x] Shows user name, email, role
- [x] Edit name field
- [x] Change password option (separate page)
- [x] Save changes button
- [x] Form validation
- [x] Success/error messages

**Story Points:** 3  
**Priority:** Medium

**Implementation Details (Commit 400d3c5, 3c52c88, Latest):**

**Backend - Controller (`app/Http/Controllers/ProfileController.php`):**
- `show()` - Display profile page via Inertia
- `updateProfile()` - Update name and email with validation
- `updatePassword()` - Change password with current password verification
- Service pattern with ProfileService injection
- Returns flash success messages on successful operations

**Backend - Service (`app/Services/ProfileService.php`):**
- `updateProfile()` - Handles profile update logic with email uniqueness check
- `updatePassword()` - Handles password change with bcrypt hashing
- Throws ValidationException for duplicate emails

**Backend - Form Requests:**
- `UpdateProfileRequest` - Validates name (required, max:255) and email (required, email, max:255)
- `ChangePasswordRequest` - Validates current_password, new_password (min:8, confirmed)
- Custom validator in ChangePasswordRequest to verify current password matches

**Backend - Routes (`routes/web.php`):**
- `GET /profile` - Show profile page (auth middleware)
- `PUT /profile` - Update profile (auth middleware)
- `GET /profile/change-password` - Show change password page (auth middleware)
- `PUT /profile/password` - Update password (auth middleware)

**Backend - Middleware (`app/Http/Middleware/HandleInertiaRequests.php`):**
- Added flash message sharing to Inertia shared data
- Shares `success` and `error` flash messages to all pages
- Critical for toast notifications to work properly

**Frontend - Profile Page (`resources/js/pages/Profile.vue`):**
- Responsive container with max-width and proper padding (mx-auto max-w-4xl)
- Profile information form (name, email, read-only role)
- Wayfinder integration for type-safe routes
- Toast notifications with auto-dismiss (5s success, 7s error)
- Error summary toast showing all validation errors
- Manual dismiss button for both success and error toasts
- Smooth slide-in/slide-out transitions
- Link to separate Change Password page in "Security Settings" section

**Frontend - Change Password Page (`resources/js/pages/ChangePassword.vue`):**
- Dedicated page for password management (max-w-2xl container)
- Current password, new password, confirm password fields
- Back link to Profile page
- Form validation with min 8 characters
- Toast notifications with auto-dismiss (5s success, 7s error)
- Error summary toast showing all validation errors
- Manual dismiss button for both success and error toasts
- Smooth slide-in/slide-out transitions
- Security tip section

**Frontend - Reusable UI Components:**
- `Input.vue` - Text/password input with error display, VeloTrack theme
- `Button.vue` - Multiple variants (primary, secondary, danger), loading states
- `Label.vue` - Form labels with required field indicator

**Frontend - Navigation (`UserProfileDropdown.vue`):**
- Enabled "Profile" link using Wayfinder `profileShow.url()`
- Added "Change Password" link to dropdown menu
- Removed "Coming Soon" disabled state

**Testing (`tests/Feature/ProfileTest.php`):**
- 18 test cases covering all scenarios
- Profile update success, validation, authorization, edge cases
- Password change success, validation, authorization, current password verification
- All tests passing

**UX Improvements:**
1. Toast notifications with prominent visual feedback (green for success, red for error)
2. Auto-dismiss timers (5 seconds for success, 7 seconds for errors)
3. Manual dismiss buttons with hover states
4. Error summary toast lists all validation errors in one place
5. Smooth animations with Vue transitions
6. Accessibility: aria-live regions for screen readers
7. Shadow and border styling for depth and visual hierarchy

**Key Design Decisions:**
1. Separated profile info and password change into two distinct pages for better UX and information architecture (separation of concerns principle)
2. Created reusable UI components (Input, Button, Label) for consistency and future use
3. Proper container styling with max-width and responsive padding to prevent edge-to-edge stretching
4. Full Wayfinder integration as best practice example for the codebase
5. Laravel service pattern for business logic separation
6. Flash message sharing via HandleInertiaRequests middleware for global toast support

---

#### US-4.5: Employee Navigation ✅ COMPLETED
**As a** employee  
**I want** easy navigation between pages  
**So that** I can access all features

**Acceptance Criteria:**
- [x] Bottom navigation bar (mobile-friendly)
- [x] Links: Dashboard, Speedometer, My Trips, Statistics (4 items)
- [x] Active state indicator
- [x] Icons for each nav item (emoji icons: 🏠 🚗 📋 📊)
- [x] Works on all employee pages

**Implementation Details:**

**Layout Architecture (`resources/js/layouts/EmployeeLayout.vue`):**
- Reusable layout wrapper for all employee pages
- Responsive navigation system (mobile + desktop)
- Content slot for page-specific content
- Proper spacing for fixed navigation elements
- Safe area inset support for notched devices

**Mobile Navigation (`resources/js/components/navigation/BottomNav.vue`):**
- Fixed bottom navigation bar (≤768px breakpoint)
- 4 primary navigation items with icon + label
- Touch-friendly targets (≥44x44px per Fitts's Law)
- Active state with cyan accent color (#22d3ee)
- Scale animation on active item (1.1x)
- Active indicator bar at bottom
- Safe area inset padding for iOS devices

**Desktop Navigation (`resources/js/components/navigation/TopNav.vue`):**
- Horizontal navigation bar (>768px breakpoint)
- VeloTrack logo/branding on left
- Navigation links with icon + label
- User profile dropdown on right
- Active state with cyan background (bg-cyan-500/10)
- Hover states with smooth transitions

**User Profile Dropdown (`resources/js/components/navigation/UserProfileDropdown.vue`):**
- User avatar with initials gradient badge
- Dropdown menu with profile and logout options
- Click outside to close functionality
- Escape key support for accessibility
- ARIA attributes for screen readers
- Profile option disabled (placeholder for US-4.4)

**Active Route Detection (`resources/js/composables/useActiveRoute.ts`):**
- Composable using Inertia's `usePage()`
- `isActive()` function with startsWith matching
- Handles nested routes automatically
- Used by both BottomNav and TopNav

**Migrated Pages:**
All employee pages wrapped with EmployeeLayout:
- `Dashboard.vue` - Removed duplicate header/logout, updated styling
- `Speedometer.vue` - Integrated with layout system
- `MyTrips.vue` - Integrated with layout system
- `TripDetail.vue` - Integrated with layout system

**Design Philosophy (UX Laws Applied):**
1. **Fitts's Law:** Touch targets ≥44x44px for easy tapping
2. **Jakob's Law:** Familiar patterns (bottom nav mobile, top nav desktop)
3. **Miller's Law:** Limited to 4 navigation items maximum
4. **Hick's Law:** Clear, focused choices without overwhelming options
5. **Law of Proximity:** Related navigation elements visually grouped

**Responsive Breakpoints:**
- Mobile: `< 768px` - Bottom navigation visible
- Desktop/Tablet: `≥ 768px` - Top navigation visible

**Navigation Items:**
```typescript
{ id: 'dashboard', label: 'Dashboard', icon: '🏠', href: '/employee/dashboard' }
{ id: 'speedometer', label: 'Speedometer', icon: '🚗', href: '/employee/speedometer' }
{ id: 'trips', label: 'My Trips', icon: '📋', href: '/employee/my-trips' }
{ id: 'statistics', label: 'Statistics', icon: '📊', href: '/employee/statistics' }
```

**Technical Details:**
- Wayfinder integration with Link component
- Smooth Inertia page transitions
- No layout shifts or flash of unstyled content
- VeloTrack dark theme consistency (#0a0c0f background)
- Gradient badges and accent colors

**Story Points:** 2  
**Priority:** High  
**Status:** ✅ Completed (April 2, 2026)

---

#### US-4.6: Responsive Design Polish
**As a** user  
**I want** the app to work well on any device  
**So that** I have good user experience

**Acceptance Criteria:**
- [ ] All pages responsive (mobile-first)
- [ ] Touch-friendly buttons (min 44px)
- [ ] Readable fonts (min 14px)
- [ ] No horizontal scrolling
- [ ] Tested on: iOS Safari, Android Chrome
- [ ] Landscape mode supported

**Story Points:** 3  
**Priority:** High

---

### Sprint 4 Tasks Breakdown

**Day 1-2:**
- US-4.1: My trips list page
- US-4.2: Trip detail page

**Day 3-4:**
- US-4.3: My statistics page
- US-4.4: Profile page

**Day 5:**
- US-4.5: Employee navigation
- US-4.6: Responsive design polish
- Cross-device testing

**Sprint 4 Total Story Points:** 23

---

## Sprint 5: Offline Functionality & PWA (Week 5)

### Sprint Goal
Implement offline capability with IndexedDB and Service Worker.

### User Stories

#### US-5.1: IndexedDB Service
**As a** developer  
**I want** IndexedDB wrapper service  
**So that** offline data can be stored locally

**Acceptance Criteria:**
- [ ] `services/indexeddb.js` created
- [ ] Database: speedTrackerDB
- [ ] Tables: trips, speedLogs, syncQueue
- [ ] CRUD methods for each table
- [ ] Error handling
- [ ] Promise-based API

**Story Points:** 5  
**Priority:** Critical

---

#### US-5.2: Offline Trip Storage
**As a** employee  
**I want** trips to be saved locally when offline  
**So that** I don't lose data without internet

**Acceptance Criteria:**
- [ ] Detect online/offline status
- [ ] When offline: save trip to IndexedDB instead of API
- [ ] Speed logs saved to IndexedDB every 5 seconds
- [ ] Visual indicator showing "Offline Mode"
- [ ] Trip marked as "pending sync"

**Story Points:** 8  
**Priority:** Critical

---

#### US-5.3: Background Sync Service
**As a** developer  
**I want** automatic sync when app comes online  
**So that** offline data is uploaded seamlessly

**Acceptance Criteria:**
- [ ] `composables/useOfflineSync.js` created
- [ ] Detects online event
- [ ] Reads pending trips from IndexedDB
- [ ] POSTs trips and speed logs to API
- [ ] Deletes from IndexedDB on success
- [ ] Retries failed syncs (max 3 attempts)
- [ ] Shows sync progress to user

**Story Points:** 8  
**Priority:** Critical

---

#### US-5.4: Service Worker Implementation
**As a** developer  
**I want** Service Worker for app caching  
**So that** app works offline

**Acceptance Criteria:**
- [ ] `public/service-worker.js` created
- [ ] Cache app shell (HTML, CSS, JS)
- [ ] Cache-first strategy for static assets
- [ ] Network-first for API calls
- [ ] Service Worker registered in main.js
- [ ] Update notification when new version available

**Story Points:** 5  
**Priority:** Critical

---

#### US-5.5: PWA Manifest Configuration
**As a** user  
**I want** to install the app on my home screen  
**So that** it feels like a native app

**Acceptance Criteria:**
- [ ] `public/manifest.json` created
- [ ] App name, short name configured
- [ ] Icons for all sizes (192x192, 512x512)
- [ ] Theme color, background color set
- [ ] Display: standalone
- [ ] Start URL configured
- [ ] Installable on iOS and Android

**Story Points:** 3  
**Priority:** High

---

#### US-5.6: Sync Queue Management UI
**As a** employee  
**I want** to see pending sync items  
**So that** I know what hasn't uploaded yet

**Acceptance Criteria:**
- [ ] Sync status badge in navigation
- [ ] Shows count of pending items
- [ ] Click to see sync queue details
- [ ] Manual "Sync Now" button
- [ ] Shows last sync timestamp

**Story Points:** 3  
**Priority:** Medium

---

### Sprint 5 Tasks Breakdown

**Day 1-2:**
- US-5.1: IndexedDB service
- US-5.2: Offline trip storage

**Day 3-4:**
- US-5.3: Background sync service
- US-5.4: Service Worker implementation

**Day 5:**
- US-5.5: PWA manifest configuration
- US-5.6: Sync queue management UI
- Offline testing scenarios

**Sprint 5 Total Story Points:** 32

---

## Sprint 6: Supervisor Dashboard (Week 6)

### Sprint Goal
Build supervisor/admin dashboard with monitoring and reporting features.

### User Stories

#### US-6.1: Dashboard Overview API
**As a** supervisor  
**I want** summary statistics via API  
**So that** I can see overall metrics

**Acceptance Criteria:**
- [ ] `GET /api/dashboard/overview` endpoint
- [ ] Returns: total trips today, active trips, violations today
- [ ] Returns: top violators (top 5)
- [ ] Returns: average speed across all employees
- [ ] Cached for 5 minutes (performance)
- [ ] Only accessible by supervisor/admin

**Story Points:** 5  
**Priority:** Critical

---

#### US-6.2: Dashboard Overview Page
**As a** supervisor  
**I want** a dashboard overview  
**So that** I can monitor at a glance

**Acceptance Criteria:**
- [ ] Dashboard.vue view created
- [ ] Stats cards: active trips, total trips, violations, avg speed
- [ ] Recent violations list
- [ ] Active trips table (real-time)
- [ ] Auto-refresh every 30 seconds
- [ ] Supervisor/admin only route

**Story Points:** 5  
**Priority:** Critical

---

#### US-6.3: All Trips Page (Supervisor)
**As a** supervisor  
**I want** to see all employee trips  
**So that** I can monitor everyone's driving

**Acceptance Criteria:**
- [ ] AllTrips.vue view created
- [ ] Shows trips from all employees
- [ ] Filter by: employee, date range, violations only
- [ ] Sort by: date, violations, distance
- [ ] Pagination (20 per page)
- [ ] Export button (triggers CSV download)
- [ ] Click trip to view details

**Story Points:** 8  
**Priority:** Critical

---

#### US-6.4: Violation Leaderboard Page
**As a** supervisor  
**I want** to see employees ranked by violations  
**So that** I can identify problematic drivers

**Acceptance Criteria:**
- [ ] ViolationLeaderboard.vue view created
- [ ] Table: rank, employee name, violation count, total trips
- [ ] Sort by violation count (desc)
- [ ] Filter by date range
- [ ] Violation rate (violations / trips) column
- [ ] Click employee to see their trips
- [ ] Not visible to employees

**Story Points:** 5  
**Priority:** High

---

#### US-6.5: Employee Management Page
**As a** admin  
**I want** to manage users  
**So that** I can add/edit/delete employees

**Acceptance Criteria:**
- [ ] Employees.vue view created
- [ ] List all users with role
- [ ] Add new user button (opens form)
- [ ] Edit user (inline or modal)
- [ ] Deactivate user (soft delete)
- [ ] Search by name/email
- [ ] Admin only access

**Story Points:** 8  
**Priority:** High

---

#### US-6.6: Settings Page
**As a** admin  
**I want** to configure app settings  
**So that** I can adjust parameters

**Acceptance Criteria:**
- [ ] Settings.vue view created
- [ ] Form fields: speed_limit, auto_stop_duration, speed_log_interval
- [ ] Save button
- [ ] Form validation
- [ ] Shows current values on load
- [ ] Success message on save
- [ ] Admin only access

**Story Points:** 3  
**Priority:** Medium

---

#### US-6.7: CSV Export Functionality
**As a** supervisor  
**I want** to export trips to CSV  
**So that** I can analyze in Excel

**Acceptance Criteria:**
- [ ] `GET /api/dashboard/reports` endpoint
- [ ] Accepts filters: date range, employee_id
- [ ] Returns CSV file download
- [ ] Columns: date, employee, duration, distance, max_speed, avg_speed, violations
- [ ] Filename: trips_export_YYYY-MM-DD.csv

**Story Points:** 5  
**Priority:** Medium

---

#### US-6.8: Supervisor Navigation
**As a** supervisor  
**I want** navigation for supervisor pages  
**So that** I can access all features

**Acceptance Criteria:**
- [ ] Sidebar navigation (desktop)
- [ ] Hamburger menu (mobile)
- [ ] Links: Dashboard, All Trips, Leaderboard, Employees, Settings
- [ ] Active state indicator
- [ ] Logout button
- [ ] User info display

**Story Points:** 3  
**Priority:** High

---

### Sprint 6 Tasks Breakdown

**Day 1-2:**
- US-6.1: Dashboard overview API
- US-6.2: Dashboard overview page

**Day 3-4:**
- US-6.3: All trips page
- US-6.4: Violation leaderboard page
- US-6.7: CSV export

**Day 5:**
- US-6.5: Employee management page
- US-6.6: Settings page
- US-6.8: Supervisor navigation

**Sprint 6 Total Story Points:** 42

---

## Sprint 7: Testing, Bug Fixes & Polish (Week 7)

### Sprint Goal
Comprehensive testing, bug fixing, and UI polish.

### Testing Tasks

#### T-7.1: Backend API Testing
- [ ] Write PHPUnit tests for all API endpoints
- [ ] Test authorization policies
- [ ] Test validation rules
- [ ] Test service layer methods
- [ ] Test edge cases (empty data, invalid inputs)
- [ ] Run tests: `php artisan test`

**Story Points:** 8  
**Priority:** Critical

---

#### T-7.2: Frontend Unit Testing
- [ ] Test composables (useGeolocation, useOfflineSync)
- [ ] Test Pinia stores (auth, trip)
- [ ] Test utility functions
- [ ] Mock API calls with MSW or similar
- [ ] Run tests: `yarn test`

**Story Points:** 5  
**Priority:** High

---

#### T-7.3: Manual Testing Scenarios
Test each scenario manually:

**Employee Flow:**
- [ ] Login as employee
- [ ] Start trip (online)
- [ ] Speed logged correctly every 5 seconds
- [ ] Violation alert triggers when exceeding limit
- [ ] Stop trip manually
- [ ] View trip in history
- [ ] View trip details with chart
- [ ] View personal statistics
- [ ] Edit profile

**Offline Flow:**
- [ ] Turn off internet
- [ ] Start trip offline
- [ ] Speed logged to IndexedDB
- [ ] Stop trip offline
- [ ] Turn on internet
- [ ] Verify auto-sync occurs
- [ ] Verify trip appears on server

**Supervisor Flow:**
- [ ] Login as supervisor
- [ ] View dashboard overview
- [ ] Check active trips
- [ ] View all trips with filters
- [ ] View violation leaderboard
- [ ] Export trips to CSV
- [ ] View employee details

**Admin Flow:**
- [ ] Login as admin
- [ ] Add new employee
- [ ] Edit existing user
- [ ] Deactivate user
- [ ] Change speed limit setting
- [ ] Verify new limit applies

**Story Points:** 8  
**Priority:** Critical

---

#### T-7.4: Cross-Browser Testing
- [ ] Chrome (Android & Desktop)
- [ ] Safari (iOS & macOS)
- [ ] Firefox (Desktop)
- [ ] Edge (Desktop)
- [ ] Test PWA installation on each

**Story Points:** 3  
**Priority:** High

---

#### T-7.5: Performance Testing
- [ ] Test with 100 trips in database
- [ ] Test with 10,000 speed logs
- [ ] Measure API response times
- [ ] Measure page load times
- [ ] Test offline sync with 10 pending trips
- [ ] Optimize slow queries

**Story Points:** 5  
**Priority:** Medium

---

#### T-7.6: Bug Fixes
- [ ] Fix all bugs found during testing
- [ ] Prioritize critical bugs first
- [ ] Document known issues (if any)

**Story Points:** 8  
**Priority:** Critical

---

#### T-7.7: UI/UX Polish
- [ ] Consistent spacing and alignment
- [ ] Loading states for all async operations
- [ ] Error messages user-friendly
- [ ] Success messages/toasts
- [ ] Smooth animations/transitions
- [ ] Accessibility improvements (keyboard navigation, ARIA labels)
- [ ] Dark mode support (optional)

**Story Points:** 5  
**Priority:** Medium

---

### Sprint 7 Tasks Breakdown

**Day 1-2:**
- T-7.1: Backend API testing
- T-7.2: Frontend unit testing

**Day 3-4:**
- T-7.3: Manual testing scenarios
- T-7.6: Bug fixes (critical)

**Day 5:**
- T-7.4: Cross-browser testing
- T-7.5: Performance testing
- T-7.7: UI/UX polish

**Sprint 7 Total Story Points:** 42

---

## Sprint 8: Deployment & Documentation (Week 8)

### Sprint Goal
Deploy to production and create documentation for users and admins.

### Deployment Tasks

#### D-8.1: Production Environment Setup
- [ ] SSH into Hostinger server
- [ ] Install PHP 8.3+, Composer, Node.js 20+
- [ ] Install MySQL 8.0+ (if not already)
- [ ] Configure Nginx virtual host
- [ ] Set up SSL certificate (Let's Encrypt)
- [ ] Configure firewall rules
- [ ] Set up cron jobs (if needed)

**Story Points:** 5  
**Priority:** Critical

---

#### D-8.2: Database Setup
- [ ] Create production database
- [ ] Create database user with privileges
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Run seeders: `php artisan db:seed --force`
- [ ] Create admin user manually
- [ ] Backup database

**Story Points:** 3  
**Priority:** Critical

---

#### D-8.3: Backend Deployment
- [ ] Clone repository to server
- [ ] Install Composer dependencies: `composer install --optimize-autoloader --no-dev`
- [ ] Configure `.env` for production
- [ ] Generate app key: `php artisan key:generate`
- [ ] Link storage: `php artisan storage:link`
- [ ] Set file permissions (775 for storage, bootstrap/cache)
- [ ] Configure Laravel Sanctum for production domain
- [ ] Test API endpoints with Postman

**Story Points:** 5  
**Priority:** Critical

---

#### D-8.4: Frontend Build & Deployment
- [ ] Update API base URL in frontend (production API)
- [ ] Build frontend: `yarn build`
- [ ] Copy `dist/` folder to server
- [ ] Configure Nginx to serve static files
- [ ] Configure Nginx to proxy API requests to Laravel
- [ ] Test frontend loads correctly
- [ ] Verify PWA manifest loads
- [ ] Test PWA installation

**Story Points:** 5  
**Priority:** Critical

---

#### D-8.5: Production Testing
- [ ] Test full employee flow (start trip, end trip)
- [ ] Test supervisor dashboard
- [ ] Test offline mode on real device
- [ ] Test sync after offline
- [ ] Test violation alerts
- [ ] Test CSV export
- [ ] Monitor logs for errors

**Story Points:** 5  
**Priority:** Critical

---

#### D-8.6: User Documentation
Create user guide covering:
- [ ] How to install PWA (iOS & Android)
- [ ] How to log in
- [ ] How to start/stop a trip
- [ ] How to view trip history
- [ ] What to do if offline
- [ ] How to interpret statistics
- [ ] FAQ section

**Story Points:** 3  
**Priority:** High

---

#### D-8.7: Admin/Supervisor Documentation
Create admin guide covering:
- [ ] How to add new users
- [ ] How to change speed limit
- [ ] How to view violation leaderboard
- [ ] How to export reports
- [ ] How to deactivate users
- [ ] System maintenance (backups, logs)

**Story Points:** 3  
**Priority:** High

---

#### D-8.8: Technical Documentation
- [ ] API documentation (endpoints, parameters, responses)
- [ ] Database schema diagram
- [ ] Deployment guide (for future updates)
- [ ] Environment variables reference
- [ ] Troubleshooting guide

**Story Points:** 3  
**Priority:** Medium

---

#### D-8.9: Monitoring Setup (Optional)
- [ ] Set up error logging (Sentry or Laravel Log Viewer)
- [ ] Set up uptime monitoring (UptimeRobot)
- [ ] Set up database backup automation
- [ ] Set up email notifications for critical errors

**Story Points:** 3  
**Priority:** Low

---

### Sprint 8 Tasks Breakdown

**Day 1-2:**
- D-8.1: Production environment setup
- D-8.2: Database setup

**Day 3-4:**
- D-8.3: Backend deployment
- D-8.4: Frontend build & deployment
- D-8.5: Production testing

**Day 5:**
- D-8.6: User documentation
- D-8.7: Admin/Supervisor documentation
- D-8.8: Technical documentation
- Launch!

**Sprint 8 Total Story Points:** 35

---

## Definition of Done (DoD)

A user story is considered "Done" when:

1. **Code Complete:**
   - [ ] Code written and committed to Git
   - [ ] Follows coding standards (PSR-12 for PHP, ESLint for JS)
   - [ ] No linter errors

2. **Tested:**
   - [ ] Unit tests written (where applicable)
   - [ ] Manual testing completed
   - [ ] No critical bugs

3. **Reviewed:**
   - [ ] Code reviewed (if team > 1)
   - [ ] Accepted by Product Owner

4. **Documented:**
   - [ ] Code comments (where necessary)
   - [ ] API endpoints documented
   - [ ] README updated (if needed)

5. **Deployed:**
   - [ ] Merged to main branch
   - [ ] Deployed to staging/production (for deployment sprints)

---

## Sprint Ceremonies

### Daily Standup (15 min)
**Time:** Every morning  
**Attendees:** Dev team  
**Format:**
- What did I complete yesterday?
- What will I work on today?
- Any blockers?

### Sprint Planning (2 hours)
**Time:** First day of sprint  
**Attendees:** Product Owner, Dev team  
**Agenda:**
1. Review sprint goal
2. Select user stories from backlog
3. Break down stories into tasks
4. Estimate story points
5. Commit to sprint

### Sprint Review (1 hour)
**Time:** Last day of sprint  
**Attendees:** Product Owner, Stakeholders, Dev team  
**Agenda:**
1. Demo completed features
2. Review sprint goal achievement
3. Gather feedback
4. Update product backlog

### Sprint Retrospective (1 hour)
**Time:** After sprint review  
**Attendees:** Dev team  
**Agenda:**
1. What went well?
2. What didn't go well?
3. What can we improve?
4. Action items for next sprint

---

## Story Point Estimation Guide

**1 Point:** Very simple task (1-2 hours)  
- Example: Add a new field to form

**2 Points:** Simple task (2-4 hours)  
- Example: Create basic CRUD API endpoint

**3 Points:** Moderate task (4-6 hours)  
- Example: Create component with some logic

**5 Points:** Complex task (1 day)  
- Example: Implement authentication system

**8 Points:** Very complex task (2 days)  
- Example: Build dashboard with multiple features

**13 Points:** Extremely complex (3+ days)  
- Example: Implement full offline sync system
- **Note:** Consider breaking into smaller stories

---

## Risk Management

### Identified Risks

**Risk 1: GPS Accuracy Issues**  
- **Impact:** High  
- **Probability:** Medium  
- **Mitigation:** Test on multiple devices, implement GPS accuracy threshold

**Risk 2: Offline Sync Conflicts**  
- **Impact:** Medium  
- **Probability:** Low  
- **Mitigation:** Timestamp-based conflict resolution, thorough testing

**Risk 3: High Battery Drain**  
- **Impact:** High  
- **Probability:** Medium  
- **Mitigation:** Optimize GPS polling interval, test battery usage, allow manual mode

**Risk 4: Server Load (100+ concurrent users)**  
- **Impact:** Medium  
- **Probability:** Low  
- **Mitigation:** Implement caching, optimize queries, monitor performance

**Risk 5: Browser Compatibility Issues**  
- **Impact:** Medium  
- **Probability:** Medium  
- **Mitigation:** Cross-browser testing, progressive enhancement

---

## Success Metrics (KPIs)

### Technical Metrics
- [ ] API response time < 500ms (95th percentile)
- [ ] Frontend page load < 2 seconds
- [ ] Offline sync success rate > 98%
- [ ] Zero critical bugs in production
- [ ] PWA Lighthouse score > 90

### User Adoption Metrics
- [ ] 80% of employees install PWA
- [ ] 90% of trips successfully recorded
- [ ] < 5 support tickets per week
- [ ] Supervisors log in at least once per day

### Business Metrics
- [ ] 20% reduction in speed violations (after 3 months)
- [ ] Increased safety awareness
- [ ] Compliance with speed policies

---

## Post-Launch Support Plan

### Week 1-2 (Stabilization)
- Daily monitoring of logs
- Quick bug fix releases
- User feedback collection
- On-call support

### Week 3-4 (Iteration)
- Analyze usage data
- Prioritize feature requests
- Minor improvements
- Documentation updates

### Month 2+ (Maintenance)
- Monthly updates
- Quarterly feature releases
- Performance monitoring
- Security updates

---

## Future Enhancements (Post-MVP)

These are potential features to consider after initial launch:

1. **Route Mapping:** Show trip route on map (optional)
2. **Gamification:** Badges for safe driving, streaks
3. **Push Notifications:** Daily/weekly reports to employees
4. **Integration:** Export to payroll/HR systems
5. **Advanced Analytics:** Predictive analytics, trends
6. **Geofencing:** Auto-start trip when leaving geofence
7. **Weather Integration:** Correlate violations with weather
8. **Multi-language Support:** Indonesian, English
9. **Dark Mode:** For night driving
10. **Voice Commands:** "Start trip", "Stop trip"

---

## Contact & Escalation

**Developer:** Zulfikar Hidayatullah  
**Phone:** +62 857-1583-8733  
**Timezone:** Asia/Jakarta (WIB)

**Escalation Path:**
1. Developer → Product Owner
2. Product Owner → Stakeholders
3. Critical issues: Immediate contact

---

## Appendix: Useful Commands

### Laravel Commands
```bash
# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Seed database
php artisan db:seed

# Run tests
php artisan test

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Generate key
php artisan key:generate
```

### Frontend Commands
```bash
# Install dependencies
yarn install

# Run dev server
yarn dev

# Build for production
yarn build

# Run tests
yarn test

# Lint code
yarn lint

# Preview production build
yarn preview
```

### Git Commands
```bash
# Create feature branch
git checkout -b feature/US-1.1-laravel-setup

# Commit changes
git add .
git commit -m "feat: implement user authentication"

# Push to remote
git push origin feature/US-1.1-laravel-setup

# Merge to main
git checkout main
git merge feature/US-1.1-laravel-setup
```

---

## Notes

- Adjust story points based on team velocity after Sprint 1-2
- Re-prioritize backlog as needed based on feedback
- Keep sprints flexible - it's okay to move stories if needed
- Focus on MVP first, enhancements later
- Document decisions and blockers

---

**Document Version:** 1.0  
**Last Updated:** March 30, 2026  
**Next Review:** End of Sprint 2
