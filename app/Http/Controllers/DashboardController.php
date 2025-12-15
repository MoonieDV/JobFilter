<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Job;
use App\Models\Notification;
use App\Models\UserSkill;
use App\Services\JobViewService;
use App\Services\ApplicationScoringService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        private JobViewService $jobViewService,
        private ApplicationScoringService $scoringService
    ) {}

    public function index()
    {
        $user = Auth::user();

        $notifications = Notification::query()
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->limit(10)
            ->get();

        $notifUnreadCount = $notifications->where('is_read', false)->count();

        $userSkills = UserSkill::query()
            ->where('user_id', $user->id)
            ->orderBy('skill_name')
            ->pluck('skill_name')
            ->filter()
            ->values();

        $userSkillsWithCategories = UserSkill::query()
            ->with('skill.category')
            ->where('user_id', $user->id)
            ->get()
            ->map(function (UserSkill $skill) {
                $category = $skill->skill?->category?->name ?? 'General';

                return [
                    'name' => $skill->skill_name,
                    'category' => $category,
                ];
            });

        $applicationsCount = Application::query()
            ->where('applicant_id', $user->id)
            ->count();

        $recentApplications = Application::query()
            ->with('job')
            ->where('applicant_id', $user->id)
            ->latest('applied_at')
            ->limit(10)
            ->get();

        $latestJobs = Job::query()
            ->active()
            ->latest('published_at')
            ->limit(10)
            ->get();

        $employerJobs = collect();
        $employerStats = [
            'activeJobs' => 0,
            'totalApplicants' => 0,
            'avgApplicantScore' => 0,
            'viewsCount' => 0,
        ];

        if (in_array($user->role, ['employer', 'admin'], true)) {
            $employerJobs = $user->postedJobs()
                ->with(['applications' => function ($query) {
                    $query->with('applicant')->latest('applied_at');
                }])
                ->withCount('applications')
                ->latest('created_at')
                ->get();

            $employerStats['activeJobs'] = $user->postedJobs()->active()->count();
            $employerStats['totalApplicants'] = Application::whereIn('job_id', $employerJobs->pluck('id'))->count();
            $employerStats['avgApplicantScore'] = $this->scoringService->getAverageScoreForEmployer($user->id);
            $employerStats['viewsCount'] = $this->jobViewService->getEmployerTotalViews($user->id);
        }

        if ($user->role === 'admin' && request()->query('admin') === '1') {
            return view('legacy.admin-dashboard', [
                'user' => $user,
            ]);
        }

        return view('legacy.dashboard', [
            'user' => $user,
            'notifications' => $notifications,
            'notifUnreadCount' => $notifUnreadCount,
            'userSkills' => $userSkills,
            'userSkillsWithCategories' => $userSkillsWithCategories,
            'applicationsCount' => $applicationsCount,
            'recentApplications' => $recentApplications,
            'latestJobs' => $latestJobs,
            'employerJobs' => $employerJobs,
            'employerStats' => $employerStats,
        ]);
    }

    public function getTotalViews()
    {
        $user = Auth::user();

        if (!in_array($user->role, ['employer', 'admin'], true)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $totalViews = $this->jobViewService->getEmployerTotalViews($user->id);

        return response()->json([
            'totalViews' => $totalViews,
        ]);
    }

    public function getAverageScore()
    {
        $user = Auth::user();

        if (!in_array($user->role, ['employer', 'admin'], true)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $averageScore = $this->scoringService->getAverageScoreForEmployer($user->id);

        return response()->json([
            'averageScore' => $averageScore,
        ]);
    }

    public function getInterviewCount()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'job_seeker') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $interviewCount = \App\Models\InterviewSchedule::query()
            ->where('applicant_id', $user->id)
            ->count();

        return response()->json([
            'interviewCount' => $interviewCount,
        ]);
    }

    public function getJobSeekerAverageMatchScore()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'job_seeker') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get job seeker's skills
        $jobSeekerSkills = $user->skills()->pluck('skill_name')->toArray();
        
        if (empty($jobSeekerSkills)) {
            return response()->json(['averageMatchScore' => 0]);
        }

        // Get all active jobs
        $activeJobs = \App\Models\Job::active()->get();
        
        if ($activeJobs->isEmpty()) {
            return response()->json(['averageMatchScore' => 0]);
        }

        $totalScore = 0;
        $jobCount = 0;

        foreach ($activeJobs as $job) {
            $requiredSkills = $job->required_skills ?? [];
            
            if (empty($requiredSkills)) {
                $totalScore += 100;
            } else {
                $matchedCount = 0;
                foreach ($requiredSkills as $requiredSkill) {
                    if (in_array(strtolower($requiredSkill), array_map('strtolower', $jobSeekerSkills))) {
                        $matchedCount++;
                    }
                }
                $score = ($matchedCount / count($requiredSkills)) * 100;
                $totalScore += $score;
            }
            $jobCount++;
        }

        $averageScore = $jobCount > 0 ? round($totalScore / $jobCount, 2) : 0;

        return response()->json([
            'averageMatchScore' => $averageScore,
        ]);
    }

    public function getRecommendedSkills()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'job_seeker') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get all active jobs and collect their required skills
        $activeJobs = \App\Models\Job::active()->get();
        $skillCount = [];

        foreach ($activeJobs as $job) {
            $requiredSkills = $job->required_skills ?? [];
            $preferredSkills = $job->preferred_skills ?? [];

            // Count required skills
            foreach ($requiredSkills as $skill) {
                $skillLower = strtolower(trim($skill));
                // Only count if skill is not empty
                if (!empty($skillLower)) {
                    $skillCount[$skillLower] = ($skillCount[$skillLower] ?? 0) + 1;
                }
            }

            // Count preferred skills
            foreach ($preferredSkills as $skill) {
                $skillLower = strtolower(trim($skill));
                // Only count if skill is not empty
                if (!empty($skillLower)) {
                    $skillCount[$skillLower] = ($skillCount[$skillLower] ?? 0) + 1;
                }
            }
        }

        // Filter skills that appear more than once (count > 1)
        $filteredSkills = array_filter($skillCount, fn ($count) => $count > 1);

        if (empty($filteredSkills)) {
            return response()->json([
                'recommendedSkills' => [],
                'debug' => [
                    'userSkills' => $userSkills,
                    'allSkillCounts' => $skillCount,
                    'activeJobsCount' => $activeJobs->count(),
                ]
            ]);
        }

        // Sort by count (descending)
        arsort($filteredSkills);

        // Format response with skills sorted by frequency
        $formattedSkills = collect($filteredSkills)->map(function ($count, $skill) {
            return [
                'name' => ucfirst($skill),
                'count' => $count,
            ];
        })->values();

        return response()->json([
            'recommendedSkills' => $formattedSkills,
        ]);
    }
}
