# Interview Scheduling Feature Implementation

## Overview
The interview scheduling feature has been fully implemented on the employer dashboard. This allows employers to schedule interviews with job applicants, send interview letters, and automatically notify applicants.

## Components Implemented

### 1. Database Migration
**File**: `database/migrations/2025_12_15_000000_create_interview_schedules_table.php`

Creates the `interview_schedules` table with the following columns:
- `id` - Primary key
- `application_id` - Foreign key to applications table
- `employer_id` - Foreign key to users table (employer)
- `applicant_id` - Foreign key to users table (job seeker)
- `scheduled_at` - DateTime of the scheduled interview
- `interview_type` - Enum: 'online' or 'physical'
- `letter_content` - Text content of the interview letter
- `status` - Status of the interview (default: 'scheduled')
- `timestamps` - Created and updated timestamps

### 2. Model
**File**: `app/Models/InterviewSchedule.php`

Relationships:
- `application()` - Belongs to Application
- `employer()` - Belongs to User (employer)
- `applicant()` - Belongs to User (applicant)

### 3. Application Model Updates
**File**: `app/Models/Application.php`

Added:
- `interview_scheduled_at` and `interview_type` to fillable array
- Cast for `interview_scheduled_at` as datetime
- `interviewSchedule()` relationship method

### 4. Controller
**File**: `app/Http/Controllers/InterviewScheduleController.php`

Methods:
- `store()` - Creates interview schedule, updates application status, and sends notification
- `show()` - Retrieves interview schedule details

Features:
- Validates interview date/time is in the future
- Generates interview letter automatically
- Updates application status to 'interview_scheduled'
- Creates notification for the applicant
- Returns JSON response for AJAX requests

### 5. Routes
**File**: `routes/web.php`

Added routes:
```php
Route::post('/interview-schedules', [InterviewScheduleController::class, 'store'])->name('interview-schedules.store');
Route::get('/interview-schedules/{interviewSchedule}', [InterviewScheduleController::class, 'show'])->name('interview-schedules.show');
```

### 6. Dashboard UI Updates
**File**: `resources/views/legacy/dashboard.blade.php`

Added:
- Interview Schedule Modal with form fields for:
  - Interview Date & Time (datetime-local input)
  - Interview Type (online/physical dropdown)
  - Interview Letter Preview
- Conditional button display:
  - Shows "Interview" button if status is not 'interview_scheduled'
  - Shows disabled "Scheduled" button if interview is already scheduled
- JavaScript functionality for:
  - Opening modal on interview button click
  - Real-time letter preview generation
  - API call to schedule interview
  - Dynamic button state updates
  - Page reload after successful scheduling

## Workflow

### Employer Side
1. Employer views applicants in the dashboard
2. Clicks "Interview" button on an applicant
3. Modal opens with date/time and interview type fields
4. Employer selects date, time, and interview type (online/physical)
5. Letter preview updates in real-time showing: "Hi! We would like to interview you on [DATE] via [TYPE]."
6. Employer clicks "Schedule Interview" button
7. API call is made to `/interview-schedules` endpoint
8. Button changes to disabled "Scheduled" state
9. Page reloads to reflect changes

### Job Seeker Side
1. Receives notification: "Your interview for [JOB TITLE] has been scheduled for [DATE]."
2. Application status updates to 'interview_scheduled'
3. Can view interview details in their applications

## Database Updates
- Application table now includes:
  - `interview_scheduled_at` (datetime)
  - `interview_type` (string: 'online' or 'physical')

## Key Features
✅ Modal popup for scheduling interviews
✅ Date/time picker with validation (must be in future)
✅ Interview type selection (Online/Physical)
✅ Real-time interview letter preview
✅ Automatic notification to job seeker
✅ Application status update to 'interview_scheduled'
✅ Dynamic button state change (Interview → Scheduled)
✅ Disabled scheduled button prevents duplicate scheduling
✅ CSRF protection on API endpoint
✅ Authorization checks (only employer can schedule)

## Testing Checklist
- [ ] Run migration: `php artisan migrate`
- [ ] Test interview button click opens modal
- [ ] Test date/time picker validation
- [ ] Test interview type selection
- [ ] Test letter preview updates in real-time
- [ ] Test schedule button submits form
- [ ] Verify application status changes to 'interview_scheduled'
- [ ] Verify notification is sent to applicant
- [ ] Verify button changes to "Scheduled" state
- [ ] Test that scheduled button is disabled
- [ ] Verify page reloads after successful scheduling

## Future Enhancements
- Add interview cancellation functionality
- Add interview rescheduling capability
- Add email notification to applicant with interview details
- Add calendar view for employer to see all scheduled interviews
- Add applicant response/confirmation for interview
- Add interview feedback/notes section
