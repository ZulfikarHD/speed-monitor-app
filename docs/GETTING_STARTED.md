# Getting Started - Speed Tracker Development

This guide will help you get started with the speed tracker application development.

---

## Prerequisites Checklist

Before starting, ensure you have the following installed:

### Required Software
- [ ] **PHP 8.3 or higher**
  ```bash
  php -v
  ```
- [ ] **Composer** (PHP dependency manager)
  ```bash
  composer --version
  ```
- [ ] **Node.js 20 or higher**
  ```bash
  node -v
  ```
- [ ] **Yarn** (preferred over npm)
  ```bash
  yarn --version
  ```
- [ ] **MySQL 8.0 or higher**
  ```bash
  mysql --version
  ```
- [ ] **Git**
  ```bash
  git --version
  ```

### IDE/Editor
- VSCode (recommended) or PHPStorm
- Extensions:
  - Volar (Vue Language Features)
  - PHP Intelephense
  - ESLint
  - Laravel Blade Snippets

---

## Project Initialization

### Step 1: Choose App Name

Before initializing, finalize your app name. Suggestions:
- **LeadFoot** (fun, memorable)
- **VeloCheck** (professional)
- **SpeedPolice** (direct)
- **ThrottleWatch** (technical)
- **OverRev** (automotive)

For this guide, we'll use `speedtracker` as placeholder. Replace with your chosen name.

---

### Step 2: Initialize Laravel 12 Backend

```bash
# Create project directory
mkdir speedtracker
cd speedtracker

# Create Laravel 12 project
composer create-project laravel/laravel backend "12.*"
cd backend

# Configure environment
cp .env.example .env
```

**Edit `.env` file:**
```env
APP_NAME=SpeedTracker
APP_ENV=local
APP_DEBUG=true
APP_TIMEZONE=Asia/Jakarta
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=speedtracker
DB_USERNAME=root
DB_PASSWORD=your_password

# Laravel Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost:5173
SESSION_DOMAIN=localhost
```

**Create database:**
```bash
mysql -u root -p
CREATE DATABASE speedtracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

**Generate app key & run initial setup:**
```bash
php artisan key:generate
php artisan storage:link
```

---

### Step 3: Install Laravel Sanctum

```bash
# Install Sanctum (should already be included in Laravel 12)
composer require laravel/sanctum

# Publish Sanctum config (if not already published)
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Run migrations
php artisan migrate
```

**Configure CORS in `config/cors.php`:**
```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'supports_credentials' => true,
```

---

### Step 4: Set Up Backend Directory Structure

Create service pattern directories:

```bash
# In backend directory
mkdir -p app/Services
mkdir -p app/Http/Requests
mkdir -p app/Policies
```

---

### Step 5: Initialize Vue 3 Frontend

```bash
# Go back to project root
cd ..

# Create Vue 3 project with Vite
yarn create vite frontend --template vue

cd frontend

# Install dependencies
yarn install

# Install additional packages
yarn add vue-router@4 pinia axios @vite-pwa/plugin vite-plugin-pwa
yarn add -D autoprefixer postcss tailwindcss
```

---

### Step 6: Configure Vue 3 Project

**Create `vite.config.js`:**
```javascript
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { VitePWA } from 'vite-plugin-pwa'

export default defineConfig({
  plugins: [
    vue(),
    VitePWA({
      registerType: 'autoUpdate',
      manifest: {
        name: 'SpeedTracker',
        short_name: 'SpeedTracker',
        description: 'Employee speed tracking and monitoring',
        theme_color: '#4F46E5',
        background_color: '#ffffff',
        display: 'standalone',
        icons: [
          {
            src: '/icon-192x192.png',
            sizes: '192x192',
            type: 'image/png'
          },
          {
            src: '/icon-512x512.png',
            sizes: '512x512',
            type: 'image/png'
          }
        ]
      }
    })
  ],
  server: {
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true
      }
    }
  }
})
```

**Initialize Tailwind CSS:**
```bash
npx tailwindcss init -p
```

**Configure `tailwind.config.js`:**
```javascript
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
```

**Create `src/assets/styles/main.css`:**
```css
@tailwind base;
@tailwind components;
@tailwind utilities;
```

**Import in `src/main.js`:**
```javascript
import './assets/styles/main.css'
```

---

### Step 7: Create Basic Frontend Structure

```bash
# In frontend/src directory
mkdir -p components/common
mkdir -p components/speedometer
mkdir -p components/dashboard
mkdir -p components/trips
mkdir -p composables
mkdir -p stores
mkdir -p services
mkdir -p views/auth
mkdir -p views/employee
mkdir -p views/supervisor
mkdir -p router
```

---

### Step 8: Initialize Git Repository

```bash
# Go back to project root
cd ..

# Initialize git
git init

# Create .gitignore at root
cat > .gitignore << 'EOF'
# OS files
.DS_Store
Thumbs.db

# IDE
.vscode/
.idea/
*.swp
*.swo

# Environment
.env
.env.local
.env.*.local

# Logs
*.log
npm-debug.log*
yarn-debug.log*
yarn-error.log*

# Dependencies
node_modules/
vendor/

# Build output
dist/
build/
EOF

# Backend-specific .gitignore
cat >> backend/.gitignore << 'EOF'
/storage/*.key
/storage/framework/
/storage/logs/
.phpunit.result.cache
EOF

# Frontend-specific .gitignore  
cat >> frontend/.gitignore << 'EOF'
.vite/
*.local
EOF

# Initial commit
git add .
git commit -m "chore: initial project setup"
```

---

## Development Workflow

### Running Development Servers

**Terminal 1 - Backend (Laravel):**
```bash
cd backend
php artisan serve
# Runs on http://localhost:8000
```

**Terminal 2 - Frontend (Vue):**
```bash
cd frontend
yarn dev
# Runs on http://localhost:5173
```

**Terminal 3 - Watch for file changes (optional):**
```bash
cd backend
php artisan queue:work --queue=default
```

---

## Sprint 1 Implementation Guide

Now that setup is complete, follow Sprint 1 tasks from SCRUM_WORKFLOW.md:

### Task 1.1: Database Migrations

Create migrations for all tables:

```bash
cd backend

# Create migrations
php artisan make:migration create_users_table # (already exists, modify)
php artisan make:migration create_trips_table
php artisan make:migration create_speed_logs_table
php artisan make:migration create_settings_table
```

**Example: `create_trips_table.php`:**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->enum('status', ['in_progress', 'completed', 'auto_stopped'])->default('in_progress');
            $table->decimal('total_distance', 10, 2)->nullable(); // km
            $table->decimal('max_speed', 8, 2)->nullable(); // km/h
            $table->decimal('average_speed', 8, 2)->nullable(); // km/h
            $table->integer('violation_count')->default(0);
            $table->integer('duration_seconds')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('synced_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'started_at']);
            $table->index('status');
            $table->index('ended_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
```

**Run migrations:**
```bash
php artisan migrate
```

---

### Task 1.2: Create Models

```bash
# Create models
php artisan make:model Trip
php artisan make:model SpeedLog
php artisan make:model Setting
```

**Example: `app/Models/Trip.php`:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    protected $fillable = [
        'user_id',
        'started_at',
        'ended_at',
        'status',
        'total_distance',
        'max_speed',
        'average_speed',
        'violation_count',
        'duration_seconds',
        'notes',
        'synced_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'synced_at' => 'datetime',
        'total_distance' => 'decimal:2',
        'max_speed' => 'decimal:2',
        'average_speed' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function speedLogs(): HasMany
    {
        return $this->hasMany(SpeedLog::class);
    }
}
```

---

### Task 1.3: Create Seeders

```bash
php artisan make:seeder UserSeeder
php artisan make:seeder SettingsSeeder
```

**Example: `database/seeders/UserSeeder.php`:**
```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@speedtracker.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Supervisor user
        User::create([
            'name' => 'Supervisor User',
            'email' => 'supervisor@speedtracker.test',
            'password' => Hash::make('password'),
            'role' => 'supervisor',
        ]);

        // Employee users (10 test employees)
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name' => "Employee {$i}",
                'email' => "employee{$i}@speedtracker.test",
                'password' => Hash::make('password'),
                'role' => 'employee',
            ]);
        }
    }
}
```

**Update `database/seeders/DatabaseSeeder.php`:**
```php
public function run(): void
{
    $this->call([
        UserSeeder::class,
        SettingsSeeder::class,
    ]);
}
```

**Run seeders:**
```bash
php artisan db:seed
```

---

### Task 1.4: Set Up Vue Router

**Create `frontend/src/router/index.js`:**
```javascript
import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/auth/Login.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: Login
    },
    {
      path: '/employee',
      name: 'employee',
      children: [
        {
          path: 'speedometer',
          name: 'speedometer',
          component: () => import('../views/employee/Speedometer.vue')
        }
      ]
    },
    {
      path: '/',
      redirect: '/login'
    }
  ]
})

export default router
```

---

### Task 1.5: Create Pinia Stores

**Create `frontend/src/stores/auth.js`:**
```javascript
import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
    isAuthenticated: false,
  }),

  getters: {
    isEmployee: (state) => state.user?.role === 'employee',
    isSupervisor: (state) => state.user?.role === 'supervisor',
    isAdmin: (state) => state.user?.role === 'admin',
  },

  actions: {
    async login(credentials) {
      try {
        const response = await axios.post('/api/auth/login', credentials)
        this.token = response.data.token
        this.user = response.data.user
        this.isAuthenticated = true
        localStorage.setItem('token', this.token)
        return true
      } catch (error) {
        console.error('Login failed:', error)
        return false
      }
    },

    async logout() {
      try {
        await axios.post('/api/auth/logout')
      } catch (error) {
        console.error('Logout failed:', error)
      } finally {
        this.user = null
        this.token = null
        this.isAuthenticated = false
        localStorage.removeItem('token')
      }
    },

    async fetchUser() {
      try {
        const response = await axios.get('/api/auth/me')
        this.user = response.data
        this.isAuthenticated = true
      } catch (error) {
        console.error('Fetch user failed:', error)
        this.logout()
      }
    }
  }
})
```

---

### Task 1.6: Configure Axios

**Create `frontend/src/services/api.js`:**
```javascript
import axios from 'axios'
import { useAuthStore } from '../stores/auth'

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
})

// Request interceptor
api.interceptors.request.use(
  (config) => {
    const authStore = useAuthStore()
    if (authStore.token) {
      config.headers.Authorization = `Bearer ${authStore.token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      const authStore = useAuthStore()
      authStore.logout()
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api
```

---

### Task 1.7: Update Main Files

**Update `frontend/src/main.js`:**
```javascript
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import './assets/styles/main.css'

const app = createApp(App)

app.use(createPinia())
app.use(router)

app.mount('#app')
```

---

## Testing Your Setup

### Backend Test

```bash
cd backend

# Run migrations
php artisan migrate:fresh --seed

# Start server
php artisan serve

# Test API (in another terminal)
curl http://localhost:8000/api/health
```

### Frontend Test

```bash
cd frontend

# Start dev server
yarn dev

# Open browser to http://localhost:5173
```

---

## Common Issues & Solutions

### Issue: "Class 'Laravel\Sanctum\Sanctum' not found"
**Solution:**
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### Issue: CORS errors in browser
**Solution:** Check `config/cors.php` and `.env` SANCTUM_STATEFUL_DOMAINS

### Issue: Database connection refused
**Solution:** 
- Ensure MySQL is running: `sudo service mysql start`
- Check database credentials in `.env`

### Issue: Port already in use
**Solution:**
- Backend: `php artisan serve --port=8001`
- Frontend: `yarn dev --port=5174`

---

## Daily Development Checklist

- [ ] Pull latest changes: `git pull`
- [ ] Check migrations: `php artisan migrate:status`
- [ ] Install new dependencies (if any): `composer install` & `yarn install`
- [ ] Start backend server: `php artisan serve`
- [ ] Start frontend server: `yarn dev`
- [ ] Check for lint errors: `yarn lint`
- [ ] Run tests before committing: `php artisan test`
- [ ] Commit changes with meaningful messages
- [ ] Push to remote: `git push`

---

## Useful Commands Reference

### Laravel
```bash
# Database
php artisan migrate                    # Run migrations
php artisan migrate:fresh --seed       # Fresh migrations + seed
php artisan db:seed                    # Run seeders only

# Code generation
php artisan make:model ModelName       # Create model
php artisan make:controller CtrlName   # Create controller
php artisan make:migration name        # Create migration
php artisan make:seeder SeederName     # Create seeder
php artisan make:request RequestName   # Create form request

# Testing
php artisan test                       # Run tests
php artisan test --filter testName     # Run specific test

# Cache
php artisan cache:clear                # Clear cache
php artisan config:clear               # Clear config
php artisan route:clear                # Clear routes
```

### Frontend
```bash
# Development
yarn dev                               # Start dev server
yarn build                             # Build for production
yarn preview                           # Preview production build

# Testing
yarn test                              # Run tests
yarn test:ui                           # Run tests with UI

# Linting
yarn lint                              # Check for lint errors
yarn lint --fix                        # Fix lint errors
```

---

## Next Steps

1. ✅ Complete project setup
2. ✅ Initialize repositories
3. 📝 Start implementing Sprint 1 user stories
4. 📝 Follow SCRUM_WORKFLOW.md for detailed tasks
5. 📝 Refer to ARCHITECTURE.md for technical specs

---

## Need Help?

- **Architecture Questions:** See [ARCHITECTURE.md](./ARCHITECTURE.md)
- **Sprint Planning:** See [SCRUM_WORKFLOW.md](./SCRUM_WORKFLOW.md)
- **Project Overview:** See [README.md](./README.md)

---

**Ready to code? Start with Sprint 1! 🚀**

Good luck with development!
