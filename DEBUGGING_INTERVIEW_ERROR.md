# Debugging Interview Scheduling Error

## Issue
When clicking "Schedule Interview", the error message shows: "Unparsed token '"-":<DOCTYPE" is not valid (504)"

This indicates the server is returning HTML instead of JSON.

## Steps to Debug

### 1. Open Browser Console
- Press `F12` to open Developer Tools
- Go to the "Console" tab

### 2. Try Scheduling an Interview Again
- Fill in the interview date/time and type
- Click "Schedule Interview"
- Check the console for logs

### 3. Look for Console Logs
You should see logs like:
```
Sending data: {application_id: "1", scheduled_at: "2025-12-15T18:00", interview_type: "online"}
Response status: 200 (or 422, 500, etc.)
Response headers: application/json
Raw response: {...}
```

### 4. Check Network Tab
- Go to "Network" tab in Developer Tools
- Try scheduling again
- Click on the "interview-schedules" request
- Check the "Response" tab to see what the server returned

## Common Issues

### Issue 1: CSRF Token Missing
If you see a 419 error, the CSRF token might not be properly set.
**Solution**: Ensure the meta tag exists: `<meta name="csrf-token" content="{{ csrf_token() }}">`

### Issue 2: Route Not Found (404)
If you see a 404 error, the route might not be registered.
**Solution**: Run `php artisan route:list | findstr interview` to verify routes exist

### Issue 3: Validation Error (422)
If you see validation errors, check what fields are failing.
**Solution**: The console will show which fields have errors

### Issue 4: Server Error (500)
If you see a 500 error, there's an exception in the controller.
**Solution**: Check the Laravel logs in `storage/logs/laravel.log`

## What to Report
Once you've debugged, please share:
1. The response status code
2. The raw response content (from console or Network tab)
3. Any error messages shown

This will help identify the exact issue.
