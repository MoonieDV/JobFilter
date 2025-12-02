<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Job;
use App\Models\Notification;
use App\Models\UserSkill;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
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

        // Employer responses to this user's applications (e.g. shortlisted, hired, or interview scheduled)
        $responsesCount = Application::query()
            ->where('applicant_id', $user->id)
            ->whereIn('status', ['shortlisted', 'hired', 'interview'])
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
            ->limit(50)
            ->get();

        // Calculate most common skills from all active jobs for recommendations
        $allActiveJobs = Job::query()
            ->active()
            ->get();
        
        $skillFrequency = [];
        foreach ($allActiveJobs as $job) {
            $requiredSkills = collect($job->required_skills ?? [])
                ->map(fn ($skill) => is_string($skill) ? trim($skill) : '')
                ->filter()
                ->unique();
            
            foreach ($requiredSkills as $skill) {
                $normalized = mb_strtolower($skill);
                if (!isset($skillFrequency[$normalized])) {
                    $skillFrequency[$normalized] = [
                        'name' => $skill, // Keep original casing
                        'count' => 0,
                    ];
                }
                $skillFrequency[$normalized]['count']++;
            }
        }
        
        // Sort by frequency and get top 5 most common skills
        $recommendedSkills = collect($skillFrequency)
            ->sortByDesc('count')
            ->take(5)
            ->pluck('name')
            ->values()
            ->all();

        // Compute how many active jobs match the user's skills (simple overlap)
        $matchingJobsCount = 0;
        $averageMatchScore = 0;
        if ($userSkills->isNotEmpty()) {
            $userSkillSet = $userSkills
                ->map(fn (string $skill) => mb_strtolower($skill))
                ->unique()
                ->values();

            $matchScores = [];

            $matchingJobsCount = $latestJobs->filter(function (Job $job) use ($userSkillSet, &$matchScores) {
                $requiredSkills = collect($job->required_skills ?? [])
                    ->map(fn ($skill) => is_string($skill) ? mb_strtolower($skill) : '')
                    ->filter()
                    ->unique();

                if ($requiredSkills->isEmpty()) {
                    return false;
                }

                $overlap = $requiredSkills->intersect($userSkillSet)->count();
                $score = (int) round(($overlap / $requiredSkills->count()) * 100);

                $matchScores[] = $score;

                // Consider it a "match" if at least one required skill overlaps
                return $overlap > 0;
            })->count();

            if (! empty($matchScores)) {
                $averageMatchScore = (int) round(array_sum($matchScores) / count($matchScores));
            }
        }

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
            $employerStats['avgApplicantScore'] = 78; // Placeholder metric for UI
            $employerStats['viewsCount'] = 156; // Placeholder metric for UI
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
            'responsesCount' => $responsesCount,
            'recentApplications' => $recentApplications,
            'latestJobs' => $latestJobs,
            'matchingJobsCount' => $matchingJobsCount,
            'averageMatchScore' => $averageMatchScore,
            'recommendedSkills' => $recommendedSkills,
            'employerJobs' => $employerJobs,
            'employerStats' => $employerStats,
        ]);
    }
}
