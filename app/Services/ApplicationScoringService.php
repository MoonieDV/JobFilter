<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Job;
use App\Models\User;

class ApplicationScoringService
{
    public function getAverageScoreForEmployer(int $employerId): float
    {
        $averageScore = Application::query()
            ->whereIn('job_id', function ($query) use ($employerId) {
                $query->select('id')
                    ->from('jobs')
                    ->where('posted_by', $employerId);
            })
            ->whereNotNull('match_score')
            ->avg('match_score');

        return round($averageScore ?? 0, 2);
    }

    public function getAverageScoreForJob(Job $job): float
    {
        $averageScore = $job->applications()
            ->whereNotNull('match_score')
            ->avg('match_score');

        return round($averageScore ?? 0, 2);
    }

    public function calculateMatchScore(Application $application): float
    {
        $job = $application->job;
        if (!$job) {
            return 0;
        }

        $skillMatchScore = $this->calculateSkillMatch($application, $job);
        $experienceScore = $this->calculateExperienceMatch($application, $job);
        $completenessScore = $this->calculateApplicationCompleteness($application);

        $totalScore = ($skillMatchScore * 0.60) + ($experienceScore * 0.25) + ($completenessScore * 0.15);

        return round(min(100, max(0, $totalScore)), 2);
    }

    protected function calculateSkillMatch(Application $application, Job $job): float
    {
        $requiredSkills = $job->required_skills ?? [];
        if (empty($requiredSkills)) {
            return 100;
        }

        // Get applicant's skills from multiple sources
        $applicantSkills = $this->getApplicantSkills($application);
        
        if (empty($applicantSkills)) {
            return 0;
        }

        $matchedCount = 0;
        foreach ($requiredSkills as $requiredSkill) {
            if (in_array(strtolower($requiredSkill), array_map('strtolower', $applicantSkills))) {
                $matchedCount++;
            }
        }

        return ($matchedCount / count($requiredSkills)) * 100;
    }

    protected function getApplicantSkills(Application $application): array
    {
        $skills = [];

        // Get skills from extracted_skills field
        if (!empty($application->extracted_skills)) {
            $skills = array_merge($skills, (array) $application->extracted_skills);
        }

        // Get skills from user_skills table
        if ($application->applicant) {
            $userSkills = $application->applicant->skills()->pluck('skill_name')->toArray();
            $skills = array_merge($skills, $userSkills);
        }

        // Remove duplicates and return
        return array_unique(array_filter($skills));
    }

    protected function calculateExperienceMatch(Application $application, Job $job): float
    {
        $jobExperience = strtolower($job->experience_level ?? '');
        $applicantExperience = strtolower($application->applicant?->job_title ?? '');

        $experienceScores = [
            'entry' => 60,
            'mid' => 80,
            'senior' => 100,
        ];

        foreach ($experienceScores as $level => $score) {
            if (str_contains($jobExperience, $level) || str_contains($applicantExperience, $level)) {
                return $score;
            }
        }

        return 70;
    }

    protected function calculateApplicationCompleteness(Application $application): float
    {
        $requiredFields = ['full_name', 'email', 'phone', 'location', 'resume_path'];
        $filledFields = 0;

        foreach ($requiredFields as $field) {
            if (!empty($application->{$field})) {
                $filledFields++;
            }
        }

        return ($filledFields / count($requiredFields)) * 100;
    }
}
