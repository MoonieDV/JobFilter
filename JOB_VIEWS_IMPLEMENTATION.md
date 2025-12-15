# Job Views Dynamic Tracking Implementation

## Overview
Implemented a dynamic job views counter on the employer dashboard that updates in real-time based on job seeker clicks/views of employer job postings.

## Components Created

### 1. Database Migration
**File**: `database/migrations/2025_12_15_000002_create_job_views_table.php`

Creates `job_views` table with:
- `id`: Primary key
- `job_id`: Foreign key to jobs table
- `user_id`: Foreign key to users table (nullable for anonymous views)
- `ip_address`: IP address of viewer (for tracking anonymous users)
- `user_agent`: Browser/device information
- `created_at`, `updated_at`: Timestamps
- Indexes on `job_id` and `user_id` for fast queries

### 2. JobView Model
**File**: `app/Models/JobView.php`

Eloquent model with relationships:
- `job()`: Belongs to Job model
- `user()`: Belongs to User model (nullable)

### 3. JobViewService
**File**: `app/Services/JobViewService.php`

Service class with three main methods:

#### `recordView(Job $job, Request $request): void`
- Records a job view when a user visits a job posting
- Prevents duplicate views from same user/IP within 1 hour
- Stores user_id (if authenticated), IP address, and user agent

#### `getJobViewsCount(Job $job): int`
- Returns total view count for a specific job
- Used for individual job view tracking

#### `getEmployerTotalViews($employerId): int`
- Returns total views across all jobs posted by an employer
- Used in employer dashboard statistics

### 4. Model Updates

#### Job Model (`app/Models/Job.php`)
Added relationship:
```php
public function views()
{
    return $this->hasMany(JobView::class);
}
```

#### JobBrowserController (`app/Http/Controllers/JobBrowserController.php`)
- Injected `JobViewService` via constructor
- Updated `show()` method to record view when job is displayed
- Calls `$this->jobViewService->recordView($job, $request)`

#### DashboardController (`app/Http/Controllers/DashboardController.php`)
- Injected `JobViewService` via constructor
- Updated employer stats calculation
- Changed from hardcoded `'viewsCount' => 156` to dynamic:
  ```php
  $employerStats['viewsCount'] = $this->jobViewService->getEmployerTotalViews($user->id);
  ```

## How It Works

1. **Job Seeker Views Job**: When a job seeker clicks on a job posting and the job details page loads
2. **View Recorded**: The `JobBrowserController->show()` method records the view via `JobViewService->recordView()`
3. **Duplicate Prevention**: Service checks if same user/IP viewed same job in last hour to prevent inflating counts
4. **Dashboard Updates**: When employer loads dashboard, `DashboardController` fetches actual view count from database
5. **Display**: The "Job Views" stat card shows the real-time count

## Features

✅ **Real-time Tracking**: Views are recorded immediately when jobs are viewed
✅ **Duplicate Prevention**: Same user/IP can't inflate view count within 1 hour
✅ **Anonymous Tracking**: Tracks views from non-authenticated users via IP address
✅ **Indexed Queries**: Database indexes ensure fast view count calculations
✅ **Per-Job Tracking**: Can track individual job performance
✅ **Employer Dashboard**: Displays total views across all employer's jobs
✅ **Scalable**: Service-based architecture allows easy extension

## Database Schema

```sql
CREATE TABLE job_views (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    job_id BIGINT NOT NULL,
    user_id BIGINT NULLABLE,
    ip_address VARCHAR(45) NULLABLE,
    user_agent TEXT NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (job_id, created_at),
    INDEX (user_id, created_at)
);
```

## Testing

To test the implementation:

1. **As Job Seeker**: 
   - Browse to jobs page
   - Click on a job to view details
   - View is recorded in database

2. **As Employer**:
   - Go to dashboard
   - Check "Job Views" stat card
   - Should show count of all views on your posted jobs
   - Increases as job seekers view your jobs

3. **Verify Database**:
   ```bash
   php artisan tinker
   >>> App\Models\JobView::count()
   >>> App\Models\JobView::where('job_id', 1)->count()
   ```

## Files Modified/Created

- ✅ `database/migrations/2025_12_15_000002_create_job_views_table.php` - Created
- ✅ `app/Models/JobView.php` - Created
- ✅ `app/Services/JobViewService.php` - Created
- ✅ `app/Models/Job.php` - Updated (added views relationship)
- ✅ `app/Http/Controllers/JobBrowserController.php` - Updated (added view recording)
- ✅ `app/Http/Controllers/DashboardController.php` - Updated (dynamic view count)

## Migration Status

✅ Migration `2025_12_15_000002_create_job_views_table` has been run successfully

## Future Enhancements

- Add view analytics by date/time
- Track view sources (search, browse, direct link)
- Add view trends/graphs
- Export view statistics
- View notifications for employers
- View filtering by job/date range
