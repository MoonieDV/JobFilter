<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ResumeStorageService;
use App\Services\SkillExtractionService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('legacy.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, ResumeStorageService $resumeStorage, SkillExtractionService $skillExtractor): RedirectResponse
    {
        try {
            $roleInput = strtolower((string) $request->input('role', 'employee'));
            $role = in_array($roleInput, ['employer', 'admin'], true) ? 'employer' : 'job_seeker';

            $rules = [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'password' => ['required', Rules\Password::defaults()],
                'role' => ['required', 'string'],
            ];

            if ($role === 'job_seeker') {
                $rules = array_merge($rules, [
                    'job_title' => ['nullable', 'string', 'max:255'],
                    'phone' => ['required', 'string', 'max:30'],
                    'dob' => ['required', 'date', 'before:today'],
                    'bio' => ['nullable', 'string'],
                    'resume' => ['nullable', 'file', 'mimes:docx', 'max:5120'],
                ]);
            } else {
                $rules = array_merge($rules, [
                    'company_name' => ['required', 'string', 'max:255'],
                    'company_reg_number' => ['required', 'string', 'max:100'],
                    'company_address' => ['required', 'string', 'max:255'],
                    'company_phone' => ['required', 'string', 'max:30'],
                    'company_linkedin' => ['nullable', 'url', 'max:255'],
                ]);
            }

            $validated = $request->validate($rules);

            $resumePath = null;
            if ($role === 'job_seeker' && $request->hasFile('resume')) {
                try {
                    $resumePath = $resumeStorage->store($request->file('resume'));
                } catch (\Throwable $e) {
                    \Log::error('Resume storage failed', ['error' => $e->getMessage()]);
                    return back()->withInput()->withErrors(['resume' => 'Failed to upload resume. Please try again.']);
                }
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $role,
                'job_title' => $validated['job_title'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'dob' => $validated['dob'] ?? null,
                'bio' => $validated['bio'] ?? null,
                'resume_path' => $resumePath,
                'company_name' => $validated['company_name'] ?? null,
                'company_reg_number' => $validated['company_reg_number'] ?? null,
                'company_address' => $validated['company_address'] ?? null,
                'company_phone' => $validated['company_phone'] ?? null,
                'company_linkedin' => $validated['company_linkedin'] ?? null,
            ]);

            event(new Registered($user));

            if ($resumePath) {
                try {
                    $skills = $skillExtractor->extract($resumePath);
                    if (! empty($skills)) {
                        $skillExtractor->persist($user->id, $skills);
                    }
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            Auth::login($user);

            return redirect(route('dashboard', absolute: false));
        } catch (\Throwable $e) {
            \Log::error('Registration failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withInput()->withErrors(['error' => 'Registration failed. Please try again.']);
        }
    }
}
