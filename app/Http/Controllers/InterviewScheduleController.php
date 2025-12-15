<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\InterviewSchedule;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InterviewScheduleController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();

        try {
            $validated = $request->validate([
                'application_id' => 'required|exists:applications,id',
                'scheduled_at' => 'required|string',
                'interview_type' => 'required|in:online,physical',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        try {
            $application = Application::with('job')->findOrFail($request->input('application_id'));

            if ($application->employer_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $scheduledAtString = $request->input('scheduled_at');
            $interviewType = $request->input('interview_type');

            // Parse the datetime string - handle both formats
            try {
                // Try parsing as ISO format first (from datetime-local input)
                $scheduledAt = \Carbon\Carbon::parse($scheduledAtString);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid date format: ' . $e->getMessage()], 422);
            }
            
            // Check if date is in the future
            if ($scheduledAt->isPast()) {
                return response()->json(['error' => 'Interview date must be in the future'], 422);
            }
            
            // Format for display in letter
            $formattedDate = $scheduledAt->format('F j, Y \a\t g:i A');
            $letterContent = "Hi! We would like to interview you on {$formattedDate} via " . ucfirst($interviewType) . ".";

            $interviewSchedule = InterviewSchedule::create([
                'application_id' => $application->id,
                'employer_id' => $user->id,
                'applicant_id' => $application->applicant_id,
                'scheduled_at' => $scheduledAt,
                'interview_type' => $interviewType,
                'letter_content' => $letterContent,
                'status' => 'scheduled',
            ]);

            $application->update([
                'status' => 'interview_scheduled',
                'interview_scheduled_at' => $scheduledAt,
                'interview_type' => $interviewType,
            ]);

            Notification::create([
                'user_id' => $application->applicant_id,
                'title' => 'Interview Scheduled',
                'message' => "Your interview for {$application->job->title} has been scheduled for {$formattedDate}.",
                'type' => 'success',
                'reference_id' => $interviewSchedule->id,
                'reference_type' => InterviewSchedule::class,
            ]);

            return response()->json([
                'status' => 'Interview scheduled successfully!',
                'interview_schedule' => $interviewSchedule,
                'application' => $application,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Request $request, InterviewSchedule $interviewSchedule)
    {
        $user = $request->user();

        if ($user->id !== $interviewSchedule->employer_id && $user->id !== $interviewSchedule->applicant_id) {
            abort(403, 'Unauthorized');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'interview_schedule' => $interviewSchedule,
                'application' => $interviewSchedule->application,
            ]);
        }

        return view('interview-schedules.show', compact('interviewSchedule'));
    }
}
