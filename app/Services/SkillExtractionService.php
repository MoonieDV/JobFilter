<?php

namespace App\Services;

use App\Models\Skill;
use App\Models\UserSkill;
use App\Models\SkillTrainingData;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use ZipArchive;

class SkillExtractionService
{
    private Collection $skills;

    public function __construct(private readonly Parser $pdfParser)
    {
        // Get skills from database
        $this->skills = Skill::query()
            ->orderByDesc('popularity_score')
            ->pluck('name')
            ->whenEmpty(fn () => collect($this->fallbackSkills()));
    }

    public function extract(string $publicPath): array
    {
        $absolutePath = Storage::disk('public')->path($publicPath);
        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

        $text = match ($extension) {
            'pdf' => $this->extractFromPdf($absolutePath),
            'docx' => $this->extractFromDocx($absolutePath),
            default => file_get_contents($absolutePath) ?: '',
        };

        $textLower = strtolower($text);

        // Get all skills to search for (database + training data)
        $allSkills = $this->getAllSkillsForExtraction();

        $foundSkills = $allSkills
            ->filter(function ($skill) use ($textLower) {
                $skillLower = strtolower($skill);
                // Simple case-insensitive substring match
                return str_contains($textLower, $skillLower);
            })
            ->unique()
            ->values()
            ->all();

        return $foundSkills;
    }

    private function getAllSkillsForExtraction(): Collection
    {
        // Get skills from training data (manually added skills by all users)
        $trainingSkills = collect();
        try {
            $trainingSkills = SkillTrainingData::query()
                ->orderByDesc('frequency')
                ->pluck('skill_name');
        } catch (\Throwable $e) {
            // If training data table doesn't exist yet, just use regular skills
            report($e);
        }

        // Merge both collections, prioritizing training data (higher frequency first)
        return $trainingSkills
            ->merge($this->skills)
            ->unique()
            ->values();
    }

    public function persist(int $userId, array $skills): void
    {
        $skills = array_slice(array_unique($skills), 0, 50);

        foreach ($skills as $skill) {
            // Try to find the skill in the database to get its ID and category
            $skillRecord = Skill::query()
                ->whereRaw('LOWER(name) = ?', [strtolower($skill)])
                ->first();

            UserSkill::updateOrCreate(
                ['user_id' => $userId, 'skill_name' => $skill],
                [
                    'skill_id' => $skillRecord?->id,
                    'confidence_score' => 1.00,
                    'extracted_from' => 'resume'
                ]
            );
        }
    }

    private function extractFromPdf(string $path): string
    {
        try {
            $pdf = $this->pdfParser->parseFile($path);
            $text = $pdf->getText();
            if (!empty(trim($text))) {
                return $text;
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return file_get_contents($path) ?: '';
    }

    private function extractFromDocx(string $path): string
    {
        $zip = new ZipArchive();
        if ($zip->open($path) === true) {
            $content = $zip->getFromName('word/document.xml') ?: '';
            $zip->close();

            return trim(strip_tags($content));
        }

        return file_get_contents($path) ?: '';
    }

    private function fallbackSkills(): array
    {
        return [
            'php',
            'javascript',
            'python',
            'java',
            'laravel',
            'symfony',
            'react',
            'vue',
            'node.js',
            'mysql',
            'postgresql',
            'mongodb',
            'docker',
            'kubernetes',
            'aws',
            'azure',
            'google cloud',
            'git',
            'jira',
            'tailwind',
            'bootstrap',
        ];
    }
}

