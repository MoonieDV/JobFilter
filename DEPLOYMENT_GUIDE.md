# JobFilter Deployment & Supabase Integration Guide

## Overview
This guide covers deploying JobFilter to Render/Railway and integrating Supabase for real-time database synchronization.

## Prerequisites
- GitHub account with your repository pushed
- Supabase account (free tier available at supabase.com)
- Render or Railway account (free tier available)

---

## Part 1: Supabase Setup

### Step 1: Create Supabase Project
1. Go to [supabase.com](https://supabase.com) and sign up
2. Click "New Project"
3. Fill in project details:
   - **Name**: jobfilter
   - **Database Password**: Create a strong password (save this!)
   - **Region**: Choose closest to your users
4. Wait for project to initialize (5-10 minutes)

### Step 2: Get Supabase Credentials
1. Go to Project Settings → API
2. Copy these values:
   - **Project URL** (SUPABASE_URL)
   - **anon public key** (SUPABASE_KEY)
   - **service_role key** (SUPABASE_SECRET_KEY)
3. Save these securely - you'll need them for deployment

### Step 3: Migrate Database to Supabase
1. In Supabase dashboard, go to SQL Editor
2. Create tables matching your Laravel migrations:

```sql
-- Users table
CREATE TABLE users (
  id BIGSERIAL PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  email_verified_at TIMESTAMP NULL,
  password VARCHAR(255) NOT NULL,
  remember_token VARCHAR(100) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Jobs table
CREATE TABLE jobs (
  id BIGSERIAL PRIMARY KEY,
  employer_id BIGINT NOT NULL REFERENCES users(id),
  title VARCHAR(255) NOT NULL,
  description TEXT,
  required_skills JSONB,
  location VARCHAR(255),
  salary_min DECIMAL(10,2),
  salary_max DECIMAL(10,2),
  published_at TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Applications table
CREATE TABLE applications (
  id BIGSERIAL PRIMARY KEY,
  job_id BIGINT NOT NULL REFERENCES jobs(id),
  applicant_id BIGINT NOT NULL REFERENCES users(id),
  status VARCHAR(50) DEFAULT 'pending',
  cover_letter TEXT,
  resume_path VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Enable Row Level Security (RLS)
ALTER TABLE users ENABLE ROW LEVEL SECURITY;
ALTER TABLE jobs ENABLE ROW LEVEL SECURITY;
ALTER TABLE applications ENABLE ROW LEVEL SECURITY;

-- Create RLS policies
CREATE POLICY "Users can view their own data" ON users
  FOR SELECT USING (auth.uid()::text = id::text);

CREATE POLICY "Jobs are viewable by all authenticated users" ON jobs
  FOR SELECT USING (auth.role() = 'authenticated');

CREATE POLICY "Applications are viewable by applicant or employer" ON applications
  FOR SELECT USING (
    auth.uid()::text = applicant_id::text OR 
    auth.uid()::text = (SELECT employer_id FROM jobs WHERE id = job_id)::text
  );
```

---

## Part 2: Laravel Configuration for Supabase

### Step 1: Update .env File
Create `.env` file in project root with:

```env
APP_NAME=JobFilter
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=pgsql
DB_HOST=your-project.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-db-password

SUPABASE_URL=https://your-project.supabase.co
SUPABASE_KEY=your-anon-key
SUPABASE_SECRET_KEY=your-service-role-key

QUEUE_CONNECTION=sync
CACHE_DRIVER=file
SESSION_DRIVER=cookie
```

### Step 2: Install Supabase PHP Client
```bash
composer require supabase/supabase-php
```

### Step 3: Create Supabase Service Class
Create `app/Services/SupabaseService.php`:

```php
<?php

namespace App\Services;

use Supabase\SupabaseClient;

class SupabaseService
{
    protected SupabaseClient $client;

    public function __construct()
    {
        $this->client = new SupabaseClient(
            config('services.supabase.url'),
            config('services.supabase.key')
        );
    }

    public function getClient(): SupabaseClient
    {
        return $this->client;
    }

    // Real-time subscriptions example
    public function subscribeToApplications(callable $callback)
    {
        $this->client
            ->from('applications')
            ->on('*', $callback)
            ->subscribe();
    }
}
```

### Step 4: Update config/services.php
Add Supabase configuration:

```php
'supabase' => [
    'url' => env('SUPABASE_URL'),
    'key' => env('SUPABASE_KEY'),
    'secret' => env('SUPABASE_SECRET_KEY'),
],
```

---

## Part 3: Deploy to Render

### Step 1: Connect GitHub to Render
1. Go to [render.com](https://render.com) and sign up
2. Click "New +" → "Web Service"
3. Connect your GitHub repository
4. Select the JobFilter repository

### Step 2: Configure Deployment Settings
1. **Name**: jobfilter
2. **Environment**: Docker
3. **Region**: Choose closest to users
4. **Plan**: Free (or paid if needed)
5. **Build Command**: 
   ```
   composer install && php artisan migrate --force && npm install && npm run build
   ```
6. **Start Command**: 
   ```
   php artisan serve --host=0.0.0.0 --port=$PORT
   ```

### Step 3: Add Environment Variables
In Render dashboard, go to Environment:
- Add all variables from your `.env` file
- Make sure to set:
  - `APP_KEY` (generate with `php artisan key:generate`)
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - All `DB_*` variables pointing to Supabase
  - All `SUPABASE_*` variables

### Step 4: Deploy
1. Click "Create Web Service"
2. Render will automatically deploy when you push to GitHub
3. Wait for build to complete (5-10 minutes)
4. Your app will be live at `https://jobfilter.onrender.com`

---

## Part 4: Real-Time Synchronization with Supabase

### Step 1: Frontend Real-Time Updates
Add to your Blade templates:

```html
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
<script>
    const supabase = window.supabase.createClient(
        '{{ env("SUPABASE_URL") }}',
        '{{ env("SUPABASE_KEY") }}'
    );

    // Subscribe to applications changes
    supabase
        .channel('applications')
        .on('postgres_changes', 
            { event: '*', schema: 'public', table: 'applications' },
            (payload) => {
                console.log('Application updated:', payload);
                // Refresh your UI here
                location.reload();
            }
        )
        .subscribe();
</script>
```

### Step 2: Backend Real-Time Events
In your Laravel controllers, broadcast events:

```php
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;

class ApplicationUpdated implements ShouldBroadcast
{
    public function broadcastOn()
    {
        return new PrivateChannel('applications.' . $this->application->id);
    }
}
```

---

## Part 5: Database Synchronization

### Option A: Automatic Sync (Recommended)
Use Laravel's queue system with Supabase webhooks:

1. In Supabase, go to Database → Webhooks
2. Create webhook for each table pointing to:
   ```
   https://your-app.onrender.com/api/webhooks/supabase
   ```
3. Create webhook handler in `app/Http/Controllers/WebhookController.php`

### Option B: Manual Sync
Run migrations to sync local database with Supabase:

```bash
php artisan migrate --database=supabase
```

---

## Part 6: Monitoring & Maintenance

### View Logs
- **Render**: Dashboard → Logs tab
- **Supabase**: Database → Logs

### Monitor Performance
- Render: Analytics tab
- Supabase: Monitoring dashboard

### Update Code
Simply push to GitHub - Render will auto-deploy

---

## Troubleshooting

### Database Connection Issues
```bash
# Test connection
php artisan tinker
>>> DB::connection()->getPdo()
```

### Migration Errors
```bash
# Run migrations manually
php artisan migrate:fresh --seed --force
```

### Real-Time Not Working
- Check Supabase RLS policies
- Verify SUPABASE_KEY is correct
- Check browser console for errors

---

## Cost Estimate (Free Tier)
- **Render**: Free tier includes 750 hours/month
- **Supabase**: Free tier includes 500MB database, unlimited API calls
- **GitHub**: Free for public repositories

**Total Cost**: $0/month (free tier)

---

## Next Steps
1. Create Supabase project
2. Migrate database schema
3. Update `.env` with Supabase credentials
4. Push to GitHub
5. Deploy to Render
6. Test real-time synchronization
7. Monitor logs and performance

For questions, refer to:
- [Supabase Docs](https://supabase.com/docs)
- [Render Docs](https://render.com/docs)
- [Laravel Docs](https://laravel.com/docs)
