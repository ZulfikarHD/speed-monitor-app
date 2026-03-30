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
│   └── useAuth.ts              (authentication logic with Inertia useHttp)
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
│   └── Welcome.vue             (default landing)
│
├── stores/                     (Pinia)
│   ├── auth.ts                 (user, token, role management)
│   └── settings.ts             (app settings)
│
├── types/
│   ├── api.ts                  (API request/response types)
│   ├── auth.ts                 (User, Auth types)
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

### Future Components (Sprint 3+)
```
resources/js/components/
├── common/
│   ├── AppHeader.vue
│   ├── LoadingSpinner.vue
│   └── AlertMessage.vue
│
├── speedometer/
│   ├── SpeedGauge.vue       (main gauge display)
│   ├── TripControls.vue     (start/stop buttons)
│   ├── TripStats.vue        (current stats)
│   └── SpeedChart.vue       (real-time chart)
│
├── dashboard/
│   ├── StatsCard.vue
│   ├── ViolationLeaderboard.vue
│   └── ActiveTripsTable.vue
│
└── trips/
    ├── TripList.vue
    ├── TripDetail.vue
    └── TripFilters.vue
```

### Future Composables (Sprint 3+)
```
resources/js/composables/
├── useAuth.ts              ✅ (implemented)
├── useGeolocation.ts       (GPS tracking logic)
├── useTrip.ts              (trip management)
└── useOfflineSync.ts       (sync logic)
```

### Authentication Flow (Implemented)

**Token-Based Auth with Inertia.js:**
1. User submits login form on `/login` (Inertia page)
2. `useAuth` composable calls Wayfinder-generated `login.url()`
3. Uses Inertia's `useHttp().post()` to call `/api/auth/login`
4. On success: token stored in localStorage via Pinia auth store
5. Inertia router navigates to role-based dashboard:
   - Employee → `/employee/dashboard`
   - Supervisor → `/supervisor/dashboard`
   - Admin → `/admin/dashboard`

**Key Implementation Details:**
- **Wayfinder** generates type-safe route functions from Laravel controllers
- **useHttp** (Inertia v3) replaces Axios for API calls
- **Pinia** manages auth state (user, token, role getters)
- **localStorage** persists token across sessions

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

**Auth Store (Implemented):**
```typescript
{
  user: User | null,
  token: string | null,
  isAuthenticated: computed(() => user !== null),
  role: computed(() => user?.role ?? null),
  isEmployee: computed(() => role === 'employee'),
  isSupervisor: computed(() => role === 'supervisor'),
  isAdmin: computed(() => role === 'admin'),
  
  // Actions
  login(user: User, token: string),
  logout(),
  setUser(user: User),
  setToken(token: string),
  initializeAuth()  // Restores token from localStorage
}
```

**Trip Store (Future - Sprint 2):**
```javascript
{
  currentTrip: null,
  speedLogs: [],
  isTracking: false,
  stats: {
    currentSpeed: 0,
    maxSpeed: 0,
    averageSpeed: 0,
    distance: 0,
    duration: 0
  }
}
```

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
