# Speed Tracker Application - Project Documentation

> **Status:** Planning Phase  
> **Developer:** Zulfikar Hidayatullah  
> **Tech Stack:** Laravel 12 API + Vue 3 PWA  
> **Target Users:** ~100-200 employees + supervisors/admins

---

## Project Overview

A web-based speed tracking and monitoring system that allows employees to track their commute sessions and enables supervisors to monitor speed compliance without invasive location tracking.

### Key Features

**For Employees:**
- Real-time speedometer with GPS tracking
- Manual trip start/stop controls
- Trip history and statistics
- Speed limit violation alerts
- Offline capability with automatic sync
- PWA installable on mobile devices

**For Supervisors/Admins:**
- Dashboard with overview statistics
- Monitor all employee trips
- Violation leaderboard (internal use)
- Export reports (CSV)
- User management
- Configurable speed limits

---

## Project Goals

1. **Privacy-Conscious:** Track speed metrics without storing location data
2. **Lightweight:** Run smoothly on 2GB RAM devices
3. **Offline-First:** Work without internet, sync when available
4. **Easy to Use:** Simple interface for non-technical users
5. **Supervisor Insights:** Clear visibility into speed compliance

---

## Documentation Structure

This project contains comprehensive planning documentation:

### 📐 [ARCHITECTURE.md](./ARCHITECTURE.md)
Complete system architecture including:
- System architecture diagram
- Database schema design
- API endpoint specifications
- Frontend architecture
- Offline strategy
- Security considerations
- Deployment architecture
- Performance optimizations
- Scalability considerations

### 📋 [SCRUM_WORKFLOW.md](./SCRUM_WORKFLOW.md)
Detailed scrum workflow with:
- 8 sprints breakdown (8 weeks total)
- User stories with acceptance criteria
- Story point estimates
- Sprint goals and task breakdowns
- Definition of Done (DoD)
- Testing strategy
- Risk management
- Success metrics (KPIs)

---

## Technology Stack

### Backend
- **Framework:** Laravel 12
- **Database:** MySQL 8.0+
- **Authentication:** Laravel Sanctum
- **Architecture:** Service Pattern (pragmatic, not over-engineered)
- **PHP Version:** 8.3+

### Frontend
- **Framework:** Vue 3 (Composition API)
- **State Management:** Pinia
- **Router:** Vue Router
- **Build Tool:** Vite
- **HTTP Client:** Axios
- **PWA:** Vite PWA Plugin
- **Styling:** TBD (Tailwind CSS recommended)

### DevOps
- **Hosting:** Hostinger
- **Web Server:** Nginx
- **SSL:** Let's Encrypt
- **Package Manager:** Yarn
- **Version Control:** Git

### Offline Technology
- **Storage:** IndexedDB
- **Caching:** Service Workers
- **Sync:** Background Sync API

---

## Database Schema

### Main Tables

**users**
- Standard user table with role (employee, supervisor, admin)

**trips**
- Tracks each commute session
- Stores: start/end time, distance, max/avg speed, violations
- Belongs to user

**speed_logs**
- Granular speed data (every 5 seconds)
- Belongs to trip
- Flag for violations

**settings**
- Global app configuration
- Speed limit, auto-stop duration, etc.

See [ARCHITECTURE.md](./ARCHITECTURE.md) for detailed schema.

---

## API Endpoints

### Authentication
- `POST /api/auth/login`
- `POST /api/auth/logout`
- `GET /api/auth/me`

### Trips
- `GET /api/trips` (list with filters)
- `POST /api/trips` (start trip)
- `PUT /api/trips/{id}` (end trip)
- `GET /api/trips/{id}` (details)
- `POST /api/trips/{id}/speed-logs` (bulk insert)

### Dashboard (Supervisor)
- `GET /api/dashboard/overview`
- `GET /api/dashboard/violations` (leaderboard)
- `GET /api/dashboard/reports` (CSV export)

### Settings (Admin)
- `GET /api/settings`
- `PUT /api/settings`

See [ARCHITECTURE.md](./ARCHITECTURE.md) for complete API specs.

---

## Development Timeline

**Total Duration:** 8 weeks (8 sprints)

| Sprint | Focus | Duration |
|--------|-------|----------|
| 1 | Project setup & Authentication | 1 week |
| 2 | Core trip management API | 1 week |
| 3 | Speedometer UI & GPS tracking | 1 week |
| 4 | Trip history & employee views | 1 week |
| 5 | Offline functionality & PWA | 1 week |
| 6 | Supervisor dashboard | 1 week |
| 7 | Testing, bug fixes & polish | 1 week |
| 8 | Deployment & documentation | 1 week |

See [SCRUM_WORKFLOW.md](./SCRUM_WORKFLOW.md) for detailed sprint planning.

---

## Project Structure (Planned)

```
speed-tracker/
├── backend/                 # Laravel 12 API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   ├── Requests/
│   │   │   └── Middleware/
│   │   ├── Models/
│   │   ├── Services/       # Business logic
│   │   └── Policies/       # Authorization
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── routes/
│   │   └── api.php
│   ├── tests/
│   └── .env.example
│
├── frontend/                # Vue 3 PWA
│   ├── public/
│   │   ├── manifest.json
│   │   └── service-worker.js
│   ├── src/
│   │   ├── components/
│   │   ├── composables/
│   │   ├── stores/         # Pinia
│   │   ├── views/
│   │   ├── router/
│   │   ├── services/
│   │   └── assets/
│   ├── package.json
│   └── vite.config.js
│
├── docs/                    # Additional documentation
├── ARCHITECTURE.md          # System architecture
├── SCRUM_WORKFLOW.md        # Sprint planning
└── README.md               # This file
```

---

## Getting Started (After Implementation)

### Prerequisites
- PHP 8.3+
- Composer
- Node.js 20+
- Yarn
- MySQL 8.0+

### Backend Setup
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

### Frontend Setup
```bash
cd frontend
yarn install
yarn dev
```

### Default Users (After Seeding)
- **Admin:** admin@example.com / password
- **Supervisor:** supervisor@example.com / password
- **Employee:** employee@example.com / password

---

## Key Features Deep Dive

### 1. Speedometer with Real-Time Tracking
- Uses browser Geolocation API
- Updates speed every second
- Visual gauge (like car speedometer)
- Color-coded: green (safe), yellow (near limit), red (violation)
- Logs speed data every 5 seconds

### 2. Speed Limit Violation System
- Global speed limit set by admin
- Real-time detection when speed exceeds limit
- Browser notification + audio alert
- Violation count tracked per trip
- Supervisor can view violation leaderboard

### 3. Offline-First Architecture
- Trips can be started offline
- Speed data stored in IndexedDB
- Automatic sync when internet available
- Background sync with retry logic
- Visual indicator of online/offline status

### 4. PWA (Progressive Web App)
- Installable on iOS and Android home screen
- Works like native app
- Offline capability
- Push notifications (for violations)
- App icon and splash screen

### 5. Supervisor Dashboard
- Real-time active trips
- Summary statistics (today, week, month)
- Violation leaderboard (internal use only)
- CSV export for reports
- Filter by employee, date range

---

## Security & Privacy

### Data Privacy
- **No GPS coordinates stored** - only speed metrics
- **No route tracking** - only distance traveled
- **Role-based access control** - employees see only their data
- **Encrypted connections** - HTTPS only in production

### Security Measures
- Laravel Sanctum for API authentication
- CSRF protection
- Rate limiting on API endpoints
- Input validation on all requests
- SQL injection prevention (Eloquent ORM)
- XSS protection (Vue auto-escaping)

---

## Performance Targets

- **API Response Time:** < 500ms (95th percentile)
- **Frontend Load Time:** < 2 seconds
- **Offline Sync Success Rate:** > 98%
- **PWA Lighthouse Score:** > 90
- **Supports:** 100-200 concurrent users initially

---

## Deployment Checklist

### Production Server Requirements
- [x] PHP 8.3+ installed
- [x] Composer installed
- [x] Node.js 20+ installed
- [x] Yarn installed
- [x] MySQL 8.0+ configured
- [x] Nginx configured
- [x] SSL certificate (Let's Encrypt)
- [ ] Environment variables set
- [ ] Database migrations run
- [ ] Default admin user created
- [ ] PWA manifest configured
- [ ] Service worker registered

See Sprint 8 in [SCRUM_WORKFLOW.md](./SCRUM_WORKFLOW.md) for detailed deployment guide.

---

## Testing Strategy

### Backend Testing (PHPUnit)
- Unit tests for services
- Feature tests for API endpoints
- Policy/authorization tests
- Database tests

### Frontend Testing
- Unit tests for composables (Vitest)
- Component tests (Vue Test Utils)
- E2E tests for critical flows (Playwright - optional)

### Manual Testing
- Cross-browser testing (Chrome, Safari, Firefox)
- Cross-device testing (iOS, Android)
- Offline scenarios
- Performance testing with realistic data

---

## Future Enhancements (Post-MVP)

Potential features to consider after initial launch:

1. **Route Mapping** - Optional map view of trip route
2. **Gamification** - Badges for safe driving, streaks
3. **Advanced Analytics** - Predictive analytics, trends
4. **Geofencing** - Auto-start trip when leaving home
5. **Weather Integration** - Correlate violations with weather
6. **Multi-language Support** - Indonesian, English
7. **Dark Mode** - For night driving
8. **Voice Commands** - "Start trip", "Stop trip"
9. **Integration** - Export to payroll/HR systems
10. **Mobile Apps** - Native iOS/Android (if budget allows)

---

## Risk Management

### Identified Risks & Mitigation

| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|------------|
| GPS accuracy issues | High | Medium | Test on multiple devices, accuracy threshold |
| Offline sync conflicts | Medium | Low | Timestamp-based resolution, thorough testing |
| High battery drain | High | Medium | Optimize GPS polling, allow manual mode |
| Server load | Medium | Low | Caching, query optimization, monitoring |
| Browser compatibility | Medium | Medium | Cross-browser testing, progressive enhancement |

---

## Success Metrics (KPIs)

### Technical Metrics
- API response time < 500ms (95th percentile)
- Frontend page load < 2 seconds
- Offline sync success rate > 98%
- Zero critical bugs in production
- PWA Lighthouse score > 90

### User Adoption Metrics
- 80% of employees install PWA
- 90% of trips successfully recorded
- < 5 support tickets per week
- Supervisors log in daily

### Business Metrics
- 20% reduction in speed violations (after 3 months)
- Increased safety awareness
- Compliance with speed policies

---

## Coding Standards

### Backend (Laravel)
- Follow PSR-12 coding standard
- Service pattern for business logic
- Form Requests for validation
- Policies for authorization
- Eloquent relationships preferred over joins
- Avoid N+1 queries (eager loading)

### Frontend (Vue)
- ESLint configured
- Composition API preferred
- Single File Components (.vue)
- Composables for reusable logic
- Pinia for state management
- Wayfinder for routing (per user rules)

### General
- Meaningful variable/function names
- Avoid unnecessary comments
- Git commit messages: conventional commits format
  - `feat: add speedometer component`
  - `fix: resolve offline sync issue`
  - `docs: update API documentation`

---

## Support & Maintenance

### Post-Launch Support Plan

**Week 1-2 (Stabilization):**
- Daily monitoring of logs
- Quick bug fix releases
- User feedback collection
- On-call support

**Week 3-4 (Iteration):**
- Analyze usage data
- Prioritize feature requests
- Minor improvements
- Documentation updates

**Month 2+ (Maintenance):**
- Monthly updates
- Quarterly feature releases
- Performance monitoring
- Security updates

---

## Contact Information

**Developer:** Zulfikar Hidayatullah  
**Phone:** +62 857-1583-8733  
**Timezone:** Asia/Jakarta (WIB)

**Project Repository:** TBD  
**Issue Tracker:** TBD  
**Documentation:** This repository

---

## License

TBD - Proprietary/Internal use

---

## Changelog

### Version 0.1.0 (Planning Phase)
- Initial project documentation created
- Architecture design completed
- Scrum workflow planned (8 sprints)
- Technology stack finalized

---

## Next Steps

1. **Finalize app naming** - Choose from: LeadFoot, VeloCheck, etc.
2. **Review documentation** - Ensure all stakeholders agree on scope
3. **Set up development environment** - Install prerequisites
4. **Initialize repositories** - Create Laravel and Vue projects
5. **Begin Sprint 1** - Authentication and project setup
6. **Schedule sprint ceremonies** - Daily standup, sprint planning, etc.

---

## Notes

- This is a living document - update as project evolves
- All technical decisions documented in ARCHITECTURE.md
- All sprint planning in SCRUM_WORKFLOW.md
- Keep stakeholders informed of major changes

---

**Document Version:** 1.0  
**Last Updated:** March 30, 2026  
**Status:** Planning Complete - Ready for Development

---

## Quick Reference

**Start Development:**
```bash
# Clone/create repositories
# Set up backend (Laravel 12)
cd backend && composer install
# Set up frontend (Vue 3)
cd frontend && yarn install
```

**Run Development Servers:**
```bash
# Backend
cd backend && php artisan serve

# Frontend
cd frontend && yarn dev
```

**Run Tests:**
```bash
# Backend
cd backend && php artisan test

# Frontend
cd frontend && yarn test
```

---

**Ready to build? Let's go! 🚀**
