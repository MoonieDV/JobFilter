<?php

namespace App\Http\Controllers;

use App\Models\UserSkill;
use App\Models\Skill;
use App\Models\SkillCategory;
use App\Models\SkillTrainingData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class SkillController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'skill_name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
        ]);

        $skillName = trim($validated['skill_name']);
        $providedCategory = isset($validated['category']) ? trim($validated['category']) : null;

        // Check if skill already exists for this user
        $existingSkill = UserSkill::where('user_id', $user->id)
            ->where('skill_name', $skillName)
            ->first();

        if ($existingSkill) {
            return response()->json(['error' => 'Skill already exists'], 422);
        }

        // Look up skill in database to get its proper category
        // Try exact match first, then case-insensitive
        $databaseSkill = Skill::with('category')->where('name', $skillName)->first();
        
        if (!$databaseSkill) {
            $databaseSkill = Skill::with('category')
                ->whereRaw('LOWER(name) = LOWER(?)', [$skillName])
                ->first();
        }
        
        // Determine category: use database category if found, otherwise use provided or default to 'Other'
        $category = 'Other';
        
        if ($databaseSkill && $databaseSkill->category) {
            $category = $databaseSkill->category->name;
            Log::info('Skill found in database', [
                'skillName' => $skillName,
                'databaseSkillName' => $databaseSkill->name,
                'categoryId' => $databaseSkill->category_id,
                'categoryName' => $category,
            ]);
        } elseif ($providedCategory) {
            $category = $providedCategory;
            Log::info('Skill not found in database, using provided category', [
                'skillName' => $skillName,
                'providedCategory' => $providedCategory,
            ]);
        } else {
            Log::info('Skill not found in database, using default category', [
                'skillName' => $skillName,
                'defaultCategory' => 'Other',
            ]);
        }

        // Create the skill with skill_id if it exists in database
        $userSkillData = [
            'user_id' => $user->id,
            'skill_name' => $skillName,
            'extracted_from' => 'manual',
        ];
        
        // If skill exists in database, link it
        if ($databaseSkill) {
            $userSkillData['skill_id'] = $databaseSkill->id;
        }
        
        $userSkill = UserSkill::create($userSkillData);

        // Track skill in training data to improve extractor
        $this->trackSkillForTraining($skillName, $category);

        // Return the skill with category
        return response()->json([
            'status' => 'Skill added successfully',
            'skill' => [
                'name' => $userSkill->skill_name,
                'category' => $category,
            ],
        ], 201);
    }

    protected function trackSkillForTraining(string $skillName, string $category): void
    {
        try {
            // Check if table exists first
            if (!Schema::hasTable('skill_training_data')) {
                Log::warning('skill_training_data table does not exist');
                return;
            }

            // Try to find existing record
            $existing = SkillTrainingData::where('skill_name', $skillName)->first();

            if ($existing) {
                // Increment frequency
                $existing->increment('frequency');
                
                // Update category if it was null/Other and now we have a better one
                if (($existing->category === null || $existing->category === 'Other') && $category !== 'Other') {
                    $existing->update(['category' => $category]);
                }
                
                Log::info('Skill frequency incremented', [
                    'skill_name' => $skillName,
                    'frequency' => $existing->frequency,
                ]);
            } else {
                // Create new record
                SkillTrainingData::create([
                    'skill_name' => $skillName,
                    'category' => $category !== 'Other' ? $category : null,
                    'frequency' => 1,
                ]);
                
                Log::info('Skill tracked for training', [
                    'skill_name' => $skillName,
                    'category' => $category,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Error tracking skill for training', [
                'skill_name' => $skillName,
                'category' => $category,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            report($e);
        }
    }

    public function getSkillsByCategory()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $userSkills = UserSkill::where('user_id', $user->id)
            ->with('skill.category')
            ->get()
            ->map(function (UserSkill $skill) {
                // First try to get category from the skill relationship
                $category = $skill->skill?->category?->name;
                
                // If not found, look up the skill in the database by name
                if (!$category) {
                    $databaseSkill = Skill::with('category')
                        ->where('name', $skill->skill_name)
                        ->first();
                    
                    if (!$databaseSkill) {
                        $databaseSkill = Skill::with('category')
                            ->whereRaw('LOWER(name) = LOWER(?)', [$skill->skill_name])
                            ->first();
                    }
                    
                    $category = $databaseSkill?->category?->name ?? 'Other';
                }
                
                return [
                    'name' => $skill->skill_name,
                    'category' => $category,
                ];
            });

        $skillsByCategory = collect($userSkills)
            ->groupBy('category')
            ->sortKeys();

        return response()->json([
            'skillsByCategory' => $skillsByCategory,
        ]);
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'skill_name' => 'required|string',
        ]);

        $deleted = UserSkill::where('user_id', $user->id)
            ->where('skill_name', $validated['skill_name'])
            ->delete();

        if (!$deleted) {
            return response()->json(['error' => 'Skill not found'], 404);
        }

        return response()->json(['status' => 'Skill removed successfully']);
    }
}
