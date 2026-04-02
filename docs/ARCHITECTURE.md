# Speed Tracker Application - System Architecture

## Overview
Web-based speed tracking system for monitoring employee commute sessions with offline capability and supervisor dashboard.

**Target Users:** ~100-200 employees + supervisors/admins  
**Tech Stack:** Laravel 12 API + Vue 3 PWA  
**Hosting:** Hostinger  
**Primary Goal:** Track speed compliance without invasive location tracking

---

## System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     CLIENT LAYER (PWA)                       │
├─────────────────────────────────────────────────────────────┤
│  ┌──────────────────┐           ┌──────────────────┐       │
│  │  Employee View   │           │  Supervisor View │       │
│  │  - Speedometer   │           │  - Dashboard     │       │
│  │  - My Trips      │           │  - All Trips     │       │
│  │  - Statistics    │           │  - Leaderboard   │       │
│  └──────────────────┘           └──────────────────┘       │
│           │                              │                   │
│           └──────────────┬───────────────┘                  │
│                          │                                   │
│              ┌───────────▼──────────┐                       │
│              │   Vue Router (SPA)   │                       │
│              └───────────┬──────────┘                       │
│                          │                                   │
│        ┌─────────────────┼─────────────────┐               │
│        │                 │                 │               │
│   ┌────▼────┐    ┌──────▼──────┐   ┌─────▼─────┐         │
│   │  Pinia  │    │ Geolocation │   │  Service  │         │
│   │  Store  │    │  Composable │   │  Worker   │         │
│   └────┬────┘    └──────┬──────┘   └─────┬─────┘         │
│        │                 │                 │               │
│        └─────────────────┼─────────────────┘               │
│                          │                                   │
│                   ┌──────▼───────┐                          │
│                   │  IndexedDB   │  (Offline Storage)       │
│                   └──────┬───────┘                          │
└──────────────────────────┼──────────────────────────────────┘
                           │
                    ┌──────▼───────┐
                    │ Sync Service │  (Background Sync)
                    └──────┬───────┘
                           │ HTTPS
                           │
┌──────────────────────────▼──────────────────────────────────┐
│                     API LAYER (Laravel 12)                   │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌─────────────────────────────────────────────────────┐   │
│  │              API Routes (Sanctum Auth)               │   │
│  │  /api/auth/*  /api/trips/*  /api/dashboard/*        │   │
│  └────────────────────────┬────────────────────────────┘   │
│                           │                                  │
│           ┌───────────────┼───────────────┐                │
│           │               │               │                │
│    ┌──────▼──────┐ ┌─────▼──────┐ ┌──────▼──────┐        │
│    │ Auth        │ │ Trip       │ │ Dashboard   │        │
│    │ Controller  │ │ Controller │ │ Controller  │        │
│    └──────┬──────┘ └─────┬──────┘ └──────┬──────┘        │
│           │               │               │                │
│    ┌──────▼──────┐ ┌─────▼──────┐ ┌──────▼──────┐        │
│    │ Auth        │ │ Trip       │ │ Dashboard   │        │
│    │ Service     │ │ Service    │ │ Service     │        │
│    └──────┬──────┘ └─────┬──────┘ └──────┬──────┘        │
│           │               │               │                │
│           └───────────────┼───────────────┘                │
│                           │                                  │
│                    ┌──────▼──────┐                          │
│                    │   Models    │                          │
│                    │  - User     │                          │
│                    │  - Trip     │                          │
│                    │  - SpeedLog │                          │
│                    └──────┬──────┘                          │
└───────────────────────────┼─────────────────────────────────┘
                            │
┌───────────────────────────▼─────────────────────────────────┐
│                    DATABASE LAYER (MySQL)                    │
├─────────────────────────────────────────────────────────────┤
│  Tables: users, trips, speed_logs, settings                 │
└─────────────────────────────────────────────────────────────┘
```

---

## Database Schema

### Users Table
```sql
users
├── id (bigint, PK)
├── name (string)
├── email (string, unique)
├── password (string, hashed)
├── role (enum: 'employee', 'supervisor', 'admin')
├── is_active (boolean, default: true)
├── created_at (timestamp)
└── updated_at (timestamp)
```

### Trips Table
```sql
trips
├── id (bigint, PK)
├── user_id (bigint, FK -> users.id)
├── started_at (datetime)
├── ended_at (datetime, nullable)
├── status (enum: 'in_progress', 'completed', 'auto_stopped')
├── total_distance (decimal, km, nullable)
├── max_speed (decimal, km/h, nullable)
├── average_speed (decimal, km/h, nullable)
├── violation_count (integer, default: 0)
├── duration_seconds (integer, nullable)
├── notes (text, nullable)
├── synced_at (datetime, nullable) -- when offline data synced
├── created_at (timestamp)
└── updated_at (timestamp)

Indexes:
- user_id, started_at
- status
- ended_at
```

### Speed Logs Table
```sql
speed_logs
├── id (bigint, PK)
├── trip_id (bigint, FK -> trips.id, onDelete: cascade)
├── speed (decimal, km/h)
├── recorded_at (datetime)
├── is_violation (boolean, default: false)
└── created_at (timestamp)

Indexes:
- trip_id, recorded_at
- is_violation

Note: Store speed data every 5 seconds during trip
```

### Settings Table
```sql
settings
├── id (bigint, PK)
├── key (string, unique)
├── value (text)
├── description (text, nullable)
├── created_at (timestamp)
└── updated_at (timestamp)

Default Settings:
- speed_limit: 60 (km/h)
- auto_stop_duration: 1800 (seconds = 30 minutes)
- speed_log_interval: 5 (seconds)
- violation_threshold: 60 (km/h, same as speed_limit initially)
```

---

## API Endpoints

### Authentication (Laravel Sanctum)
```
POST   /api/auth/register          - Register new user (admin only)
POST   /api/auth/login             - Login
POST   /api/auth/logout            - Logout
GET    /api/auth/me                - Get current user
PUT    /api/auth/profile           - Update profile
```

### Trips (Employee + Supervisor)
```
GET    /api/trips                  - List trips (paginated)
                                     Employee: own trips only
                                     Supervisor: all trips
                                     Filters: user_id, date_from, date_to, status

POST   /api/trips                  - Start new trip
PUT    /api/trips/{id}             - Update trip (end trip)
GET    /api/trips/{id}             - Get trip details with speed logs
DELETE /api/trips/{id}             - Delete trip (supervisor/admin only)

POST   /api/trips/{id}/speed-logs  - Bulk insert speed logs (offline sync)
                                     Body: [{speed, recorded_at}, ...]
```

### Dashboard (Supervisor/Admin)
```
GET    /api/dashboard/overview     - Summary stats (today, this week)
GET    /api/dashboard/violations   - Violation leaderboard
GET    /api/dashboard/active-trips - Currently active trips
GET    /api/dashboard/reports      - Generate reports (CSV export)
```

### Statistics (Employee)
```
GET    /api/statistics/my-stats    - Personal statistics
                                     - Total trips
                                     - Total distance
                                     - Average speed
                                     - Violation count
```

### Users (Admin/Supervisor)
```
GET    /api/users                  - List all users (paginated)
GET    /api/users/{id}             - Get user details
POST   /api/users                  - Create user (admin only)
PUT    /api/users/{id}             - Update user (admin only)
DELETE /api/users/{id}             - Delete/deactivate user (admin only)
```

### Settings (Admin)
```
GET    /api/settings               - Get all settings
PUT    /api/settings               - Update settings (bulk)
```

---

## Frontend Architecture (Inertia.js v3 + Vue 3)

**Key Technologies:**
- **Inertia.js v3:** Server-side routing with client-side SPA feel
- **Laravel Wayfinder:** Type-safe route generation for frontend
- **Pinia:** State management
- **Tailwind CSS v4:** Styling
- **TypeScript:** Type safety

### Directory Structure (Actual Implementation)
```
resources/js/
├── actions/                    (Wayfinder generated - type-safe routes)
│   └── App/Http/Controllers/
│       └── Auth/
│           └── AuthController.ts   (login, logout, me functions)
│
├── composables/
│   ├── useAuth.ts              (authentication logic with Inertia router)
│   └── useGeolocation.ts       (GPS tracking for speed monitoring)
│
├── pages/                      (Inertia.js pages)
│   ├── auth/
│   │   └── Login.vue           (login form with validation)
│   ├── employee/
│   │   └── Dashboard.vue       (employee landing page)
│   ├── supervisor/
│   │   └── Dashboard.vue       (supervisor landing page)
│   ├── admin/
│   │   └── Dashboard.vue       (admin landing page)
│   ├── test/                   ✅ (Development testing pages)
│   │   ├── GeolocationTest.vue (GPS testing page)
│   │   └── SpeedGaugeDemo.vue  ✅ (SpeedGauge interactive demo)
│   └── Welcome.vue             (default landing)
│
├── stores/                     (Pinia)
│   ├── auth.ts                 (user, token, role management)
│   ├── settings.ts             (app settings)
│   └── trip.ts                 (trip tracking and speed log management)
│
├── types/
│   ├── api.ts                  (API request/response types)
│   ├── auth.ts                 (User, Auth types)
│   ├── geolocation.ts          (GPS and speed tracking types)
│   ├── speedometer.ts          ✅ (SpeedGauge props, colors, coordinates types)
│   ├── trip.ts                 (Trip, SpeedLog, TripStats types)
│   ├── index.ts                (barrel exports)
│   └── global.d.ts             (global type declarations)
│
├── lib/
│   └── utils.ts                (utility functions like cn)
│
├── app.ts                      (Inertia app bootstrap)
└── wayfinder.ts                (Wayfinder runtime types)

routes/
├── web.php                     (Inertia routes)
└── api.php                     (API endpoints)
```

### Components Structure
```
resources/js/components/
├── common/                  (Future - Sprint 4+)
│   ├── AppHeader.vue
│   ├── LoadingSpinner.vue
│   └── AlertMessage.vue
│
├── speedometer/            (Sprint 3 - Active Development)
│   ├── SpeedGauge.vue       ✅ (US-3.3: 270° SVG gauge with color zones)
│   ├── TripControls.vue     (US-3.4: start/stop buttons)
│   ├── TripStats.vue        (US-3.5: real-time statistics)
│   └── SpeedChart.vue       (Future: speed over time chart)
│
├── dashboard/              (Future - Sprint 6)
│   ├── StatsCard.vue
│   ├── ViolationLeaderboard.vue
│   └── ActiveTripsTable.vue
│
└── trips/                  (Future - Sprint 4)
    ├── TripList.vue
    ├── TripDetail.vue
    └── TripFilters.vue
```

**SpeedGauge Component (Implemented - US-3.3):**
- **Location:** `resources/js/components/speedometer/SpeedGauge.vue`
- **Purpose:** Visual speedometer displaying real-time speed with color-coded feedback
- **Features:**
  - 270° circular SVG arc gauge (resolution-independent)
  - Dynamic color zones: green (safe), yellow (warning), red (violation)
  - Speed limit marker on arc
  - Large central speed display with unit label
  - Smooth CSS transitions (500ms)
  - Responsive sizing: sm (192px), md (256-320px), lg (320-384px)
  - Full ARIA accessibility with live regions
  - Integration with settings store for dynamic speed_limit
- **Props:**
  - `speed: number` - Current speed in km/h
  - `speedLimit?: number` - Speed limit threshold (default: 60)
  - `maxSpeed?: number` - Gauge maximum range (default: 120)
  - `size?: 'sm' | 'md' | 'lg'` - Responsive size preset (default: 'md')
- **Dependencies:** settings store, speedometer types
- **Demo Page:** `resources/js/pages/test/SpeedGaugeDemo.vue` (interactive testing)

### Composables (Implemented)
```
resources/js/composables/
├── useAuth.ts              ✅ (Sprint 1 - authentication & logout)
└── useGeolocation.ts       ✅ (Sprint 3 - GPS tracking with speed monitoring)

Future:
├── useOfflineSync.ts       (Sprint 5 - offline sync logic)
└── useNotification.ts      (Sprint 3 - violation alerts)
```

### Authentication Flow (Implemented - Sprint 1)

**Token-Based Auth with Inertia.js + Wayfinder:**

**Login Flow:**
1. User visits `/login` (Inertia page route)
2. `Login.vue` component renders with email/password form
3. User enters credentials and submits
4. Client-side validation runs (email format, password length)
5. If valid, `form.submit(loginAction())` called with Wayfinder route object
6. Inertia POST to `/api/auth/login` via backend controller
7. Backend validates credentials, generates Sanctum token
8. Backend returns Inertia response with user + token as props
9. Frontend `onSuccess` callback receives user + token
10. Data stored in Pinia auth store → persisted to localStorage
11. Inertia router navigates to role-based dashboard:
    - Employee → `/employee/dashboard`
    - Supervisor → `/supervisor/dashboard`
    - Admin → `/admin/dashboard`

**Logout Flow:**
1. User clicks logout button on dashboard
2. `useAuth().handleLogout()` called
3. Inertia router POST to `/api/auth/logout` with Wayfinder: `router.post(logoutAction())`
4. Backend revokes Sanctum token
5. Backend returns redirect response to `/login`
6. Frontend `onFinish` callback clears Pinia auth store
7. localStorage cleared (user + token removed)
8. Redirects to login page

**Session Persistence (Page Refresh):**
1. App loads → `app.ts` initializes Pinia
2. `authStore.initializeAuth()` called immediately
3. Reads `auth_token` and `auth_user` from localStorage
4. If both exist, hydrates store state
5. User remains logged in across refreshes

**Key Implementation Details:**
- **Wayfinder** generates type-safe route functions from Laravel controllers
- **useForm** (Inertia v3) used for login form submission, NOT useHttp
- **router.post()** (Inertia v3) used for logout, accepts route objects
- **Pinia** manages auth state (user, token, role getters)
- **localStorage** persists BOTH user object and token across sessions
- **Sanctum** provides token-based API authentication
- **Full Documentation** with PHPDoc and JSDoc throughout

**Wayfinder Usage Patterns:**
```typescript
// Login: form.submit() with route object
form.submit(loginAction())

// Logout: router.post() with route object
router.post(logoutAction(), {}, { headers: {...} })

// Note: Different from useHttp which needs .url()
// http.post(loginAction.url(), data)  // if using useHttp
```

**File Structure:**
```
Backend:
├── app/Http/Controllers/Auth/AuthController.php (login, logout, me)
├── app/Services/AuthService.php (business logic)
└── app/Http/Requests/Auth/LoginRequest.php (validation)

Frontend:
├── resources/js/pages/auth/Login.vue (login form)
├── resources/js/composables/useAuth.ts (logout logic)
├── resources/js/stores/auth.ts (state management)
├── resources/js/types/api.ts (TypeScript types)
├── resources/js/actions/App/Http/Controllers/Auth/AuthController.ts (Wayfinder generated)
└── resources/js/app.ts (Pinia init + auth restore)
```

### Laravel Wayfinder Integration

**What is Wayfinder?**
Laravel Wayfinder auto-generates TypeScript functions for all Laravel routes and controller actions, providing type-safe route access in the frontend.

**Usage Example:**
```typescript
// Import Wayfinder-generated functions
import { login, logout, me } from '@/actions/App/Http/Controllers/Auth/AuthController'

// Type-safe API calls
const response = await http.post(login.url(), credentials)
await http.post(logout.url())
const user = await http.get(me.url())
```

**Benefits:**
- ✅ Type safety: TypeScript knows all available routes
- ✅ Auto-completion: IDE suggests available routes
- ✅ Refactor-safe: Renaming controller breaks frontend at compile time
- ✅ No hardcoded URLs: Routes generated from Laravel definitions

### State Management (Pinia Stores)

**Auth Store (Implemented - Sprint 1):**
```typescript
// Location: resources/js/stores/auth.ts
{
  // State
  user: User | null,             // Current authenticated user
  token: string | null,          // Sanctum API token
  
  // Computed Getters
  isAuthenticated: computed(() => user !== null),
  role: computed(() => user?.role ?? null),
  isEmployee: computed(() => role === 'employee'),
  isSupervisor: computed(() => role === 'supervisor'),
  isAdmin: computed(() => role === 'admin'),
  
  // Actions
  login(user: User, token: string),    // Store user + token, persist to localStorage
  logout(),                             // Clear state, remove from localStorage
  setUser(user: User | null),          // Update user, persist to localStorage
  setToken(token: string | null),      // Update token, persist to localStorage
  initializeAuth()                      // Restore user + token from localStorage on app startup
}
```

**Key Features:**
- ✅ Both user object AND token persisted to localStorage
- ✅ Automatic session restore on page refresh
- ✅ Called in `app.ts` during Inertia initialization
- ✅ Role-based computed properties for access control
- ✅ Comprehensive JSDoc documentation

**Trip Store (Implemented - Sprint 3):**
```typescript
// Location: resources/js/stores/trip.ts
{
  // Core State
  currentTrip: Trip | null,           // Active trip session
  speedLogs: SpeedLog[],              // Buffered speed logs (sync every 10)
  isTracking: boolean,                // Tracking status
  stats: TripStats,                   // Real-time statistics
  
  // Loading States
  isStarting: boolean,
  isEnding: boolean,
  isSyncing: boolean,
  
  // Metadata
  lastSyncAt: Date | null,            // Last successful sync timestamp
  error: string | null,               // Current error message
  
  // Actions
  startTrip(notes?: string),          // POST /api/trips
  addSpeedLog(speed, timestamp),      // Buffer speed logs locally
  syncSpeedLogs(),                    // Bulk POST /api/trips/{id}/speed-logs
  endTrip(notes?: string),            // PUT /api/trips/{id}
  clearTrip(),                        // Reset state
  
  // Computed
  hasActiveTrip,                      // Boolean: trip in progress
  pendingLogCount,                    // Number of logs awaiting sync
  needsSync                           // Boolean: ≥10 logs (50s data)
}
```

**Key Features:**
- **Speed Log Batching**: Buffers 10 logs (~50 seconds) before syncing to reduce API calls by ~90%
- **Real-time Statistics**: Calculates max/avg speed, distance, violations locally for immediate feedback
- **Retry Logic**: Exponential backoff (1s, 2s, 4s) with max 3 attempts for failed syncs
- **Violation Detection**: Automatic comparison against configurable speed_limit from settings store
- **Type Safety**: Full TypeScript with types matching Laravel backend models exactly

**Sync Store (Future - Sprint 5):**
```javascript
{
  syncQueue: [],        // pending trips/logs to sync
  isSyncing: false,
  lastSyncAt: null,
  isOnline: true
}
```

---

## Offline Strategy

### IndexedDB Schema
```javascript
Databases:
  - speedTrackerDB
    Tables:
      - trips          // local trip storage
      - speedLogs      // local speed log storage
      - syncQueue      // items pending sync
```

### Service Worker Strategy
```
1. Cache Strategy:
   - App Shell: Cache-First (HTML, CSS, JS, icons)
   - API Calls: Network-First (fallback to IndexedDB)
   - Static Assets: Cache-First

2. Background Sync:
   - When online: sync pending trips every 30 seconds
   - On reconnect: immediate sync
   - Retry failed syncs with exponential backoff

3. Offline Flow:
   Employee starts trip (offline) →
   Speed logs saved to IndexedDB →
   Trip ends → stored locally →
   App comes online → Background Sync →
   Data POSTed to API → IndexedDB cleared
```

---

## Performance Optimizations

### Frontend
1. **Lazy Loading:** Route-based code splitting
2. **Virtual Scrolling:** For long trip lists
3. **Debounced Inputs:** Search/filter inputs
4. **Memoization:** Computed values for stats
5. **Image Optimization:** WebP format for icons

### Backend
1. **Eager Loading:** Reduce N+1 queries
2. **Query Optimization:** Indexes on frequently filtered columns
3. **Caching:** Redis for dashboard stats (optional)
4. **Pagination:** Default 20 items per page
5. **Bulk Inserts:** Speed logs inserted in batches

### Database
1. **Indexes:** On user_id, started_at, status, is_violation
2. **Partitioning:** Consider partitioning speed_logs by date (if >1M rows)
3. **Archiving:** Move old trips (>1 year) to archive table

---

## Security Considerations

1. **Authentication:** Laravel Sanctum (SPA token)
2. **Authorization:** Policy-based (TripPolicy, UserPolicy)
3. **Rate Limiting:** API throttling (60 requests/minute)
4. **Input Validation:** Form Requests for all inputs
5. **CORS:** Restrict to frontend domain only
6. **HTTPS Only:** Enforce in production
7. **XSS Protection:** Vue auto-escapes, CSP headers
8. **SQL Injection:** Eloquent ORM prevents

---

## Deployment Architecture

```
┌─────────────────────────────────────────┐
│         Hostinger Server                 │
├─────────────────────────────────────────┤
│                                          │
│  ┌────────────────────────────────┐    │
│  │  Nginx                          │    │
│  │  - SSL/TLS (Let's Encrypt)      │    │
│  │  - Reverse Proxy                │    │
│  └───────┬────────────────┬────────┘    │
│          │                 │             │
│  ┌───────▼────────┐  ┌────▼──────────┐ │
│  │  Static Files  │  │  Laravel 12   │ │
│  │  (Vue Build)   │  │  (PHP 8.3+)   │ │
│  │  /var/www/app  │  │  /var/www/api │ │
│  └────────────────┘  └────┬──────────┘ │
│                           │             │
│                      ┌────▼──────────┐  │
│                      │  MySQL 8.0+   │  │
│                      └───────────────┘  │
│                                          │
└─────────────────────────────────────────┘
```

### Deployment Checklist
- [ ] PHP 8.3+ installed
- [ ] Composer installed
- [ ] Node.js 20+ installed
- [ ] MySQL 8.0+ configured
- [ ] SSL certificate (Let's Encrypt)
- [ ] Environment variables configured
- [ ] Database migrations run
- [ ] Seeders run (default admin user)
- [ ] Service worker registered
- [ ] PWA manifest configured

---

## Scalability Considerations

**Current Architecture (100-200 users):**
- Single server (Hostinger)
- MySQL on same server
- No caching layer

**Future Scaling (1000+ users):**
1. **Add Redis:** Cache dashboard stats, session storage
2. **Queue System:** Laravel Queues for background sync processing
3. **CDN:** Serve static assets via CDN
4. **Database Replication:** Read replicas for analytics
5. **Load Balancer:** Multiple app servers behind LB
6. **Separate DB Server:** Dedicated MySQL instance

---

## Monitoring & Logging

### Application Logs
- Laravel Log: `/storage/logs/laravel.log`
- Error tracking: Consider Sentry (optional)

### Metrics to Track
- Active trips count
- API response times
- Failed sync attempts
- Violation rate
- Average trip duration

### Health Checks
- Database connection
- Disk space
- API uptime
- Background sync queue

---

## Testing Strategy

### Backend (Laravel)
1. **Unit Tests:** Services, Models
2. **Feature Tests:** API endpoints, Authorization
3. **Database Tests:** Migrations, Seeders

### Frontend (Vue)
1. **Unit Tests:** Composables, Stores (Vitest)
2. **Component Tests:** Vue Test Utils
3. **E2E Tests:** Playwright (optional for critical flows)

### Manual Testing Checklist
- [ ] Start trip (online)
- [ ] Start trip (offline)
- [ ] Speed logging accuracy
- [ ] Auto-stop after 30 min
- [ ] Offline sync on reconnect
- [ ] Violation detection
- [ ] Dashboard stats accuracy
- [ ] PWA installation
- [ ] Cross-browser testing (Chrome, Safari, Firefox)

---

## Development Timeline Estimate

See SCRUM_WORKFLOW.md for detailed sprint planning.

**Rough Estimate:**
- Sprint 1-2: Backend API + Database (2 weeks)
- Sprint 3-4: Frontend Core (Speedometer, Auth) (2 weeks)
- Sprint 5: Offline Sync + PWA (1 week)
- Sprint 6: Dashboard + Admin (1 week)
- Sprint 7: Testing + Bug Fixes (1 week)
- Sprint 8: Deployment + Documentation (1 week)

**Total: ~8 weeks** (can be compressed with parallel work)

---

## Next Steps

1. Review this architecture document
2. Review SCRUM_WORKFLOW.md for development plan
3. Finalize app naming
4. Set up development environment
5. Initialize Laravel 12 + Vue 3 projects
6. Begin Sprint 1 development
