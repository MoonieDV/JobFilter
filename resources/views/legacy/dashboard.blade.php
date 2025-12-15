@php
    use Illuminate\Support\Str;

    $role = $user->role ?? 'job_seeker';
    $skillsByCategory = collect($userSkillsWithCategories)
        ->groupBy(fn ($skill) => $skill['category'] ?? 'General')
        ->sortKeys();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - JobFilter</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('legacy/Images/log.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('legacy/css/home.css') }}" rel="stylesheet">
    <link href="{{ asset('legacy/css/dashboard.css') }}" rel="stylesheet">
</head>
<body data-user-role="{{ $role }}">
    <nav class="navbar navbar-light bg-light sticky-top shadow-sm navbar-glass">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('landing') }}">JobFilter</a>
            <div class="d-flex align-items-center">
                <div class="dropdown me-3" data-bs-auto-close="outside">
                    <a class="nav-link position-relative" href="#" role="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ $notifUnreadCount }}
                            <span class="visually-hidden">unread notifications</span>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
                        <li class="d-flex align-items-center justify-content-between px-3 py-2">
                            <h6 class="mb-0">Notifications</h6>
                            <button id="notifClearAll" class="btn btn-sm btn-outline-secondary" data-mark-read="{{ route('notifications.readAll') }}">Clear All</button>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        @forelse ($notifications as $notification)
                            <li class="notification-item">
                                <div class="dropdown-item d-flex align-items-start justify-content-between gap-2">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-{{ $notification->type === 'danger' ? 'exclamation-circle text-danger' : ($notification->type === 'success' ? 'check-circle text-success' : 'info-circle text-primary') }}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 fw-semibold">{{ $notification->title }}</p>
                                        <small class="text-muted">{{ $notification->message }}</small>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="notification-item">
                                <div class="dropdown-item text-muted">No notifications</div>
                            </li>
                        @endforelse
                    </ul>
                </div>
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#navMenu" aria-controls="navMenu" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
            <div class="offcanvas offcanvas-end" tabindex="-1" id="navMenu" aria-labelledby="navMenuLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="navMenuLabel">Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <div class="text-center mb-3">
                        <img id="offcanvasAvatar" src="{{ asset('legacy/Images/log.png') }}" class="rounded-circle mb-2" width="72" height="72" alt="Avatar">
                        <h5 class="fw-bold mb-2">{{ $user->name }}</h5>
                        <p class="text-muted small mb-2">{{ $user->email }}</p>
                        <p class="text-muted small mb-2">Role: {{ Str::headline($role) }}</p>
                        <button class="btn btn-outline-danger btn-sm" id="logoutBtn">Logout</button>
                        <hr class="mt-3">
                    </div>
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0" id="navMenuList">
                        <li class="nav-item"><a class="nav-link active" href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('jobs.browse') }}">Find Jobs</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('legacy.about') }}">About</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('legacy.contact') }}">Contact</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('profile.edit') }}">Profile</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <section class="py-4 bg-primary text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-12 col-md-8">
                    <h1 class="fw-bold mb-2">Dashboard</h1>
                    <p class="mb-0">Welcome back, {{ $user->name }}. Here's what's happening with your search.</p>
                </div>
                <div class="col-12 col-md-4 text-md-end">
                    <div class="d-flex justify-content-md-end gap-2">
                        <button class="btn btn-light btn-sm" id="jobSeekerBtn" onclick="showRoleSwitchModal('job_seeker')">Job Seeker</button>
                        <button class="btn btn-outline-light btn-sm" id="employerBtn" onclick="showRoleSwitchModal('employer')">Employer</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div id="jobSeekerDashboard">
                <div class="row mb-4">
                    <div class="col-12 col-md-3 mb-3">
                        <div class="card stat-card text-center">
                            <div class="card-body">
                                <div class="stat-icon mb-2">📊</div>
                                <h3 class="stat-number">{{ $applicationsCount }}</h3>
                                <p class="stat-label">Applications</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 mb-3">
                        <div class="card stat-card text-center">
                            <div class="card-body">
                                <div class="stat-icon mb-2">🎯</div>
                                <h3 class="stat-number">{{ $matchingJobsCount }}</h3>
                                <p class="stat-label">New Matches</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 mb-3">
                        <div class="card stat-card text-center" data-stat="avg-match-score">
                            <div class="card-body">
                                <div class="stat-icon mb-2">📈</div>
                                <h3 class="stat-number">{{ $averageMatchScore }}%</h3>
                                <p class="stat-label">Avg. Match Score</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 mb-3">
                        <div class="card stat-card text-center" data-stat="responses">
                            <div class="card-body">
                                <div class="stat-icon mb-2">📧</div>
                                <h3 class="stat-number">{{ $responsesCount }}</h3>
                                <p class="stat-label">Responses</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12 col-lg-8 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-header bg-gradient border-0 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <div>
                                    <h5 class="mb-0 text-white fw-bold">Your Skills Profile</h5>
                                    <small class="text-white-50">Grouped by category</small>
                                </div>
                                <button class="btn btn-light btn-sm rounded-pill fw-semibold" data-bs-toggle="modal" data-bs-target="#addSkillModal">
                                    <i class="bi bi-plus-circle me-1"></i>Add Skill
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="skills-container" id="skillsContainer" style="max-height:300px;overflow-y:auto;">
                                    @forelse ($skillsByCategory as $category => $skills)
                                        <div class="mb-4 skill-category" data-category="{{ $category }}">
                                            <div class="px-3 py-2 bg-light border-start border-4 border-primary rounded-lg d-flex justify-content-between align-items-center mb-2">
                                                <span class="fw-bold text-dark">{{ $category }}</span>
                                                <span class="badge bg-primary text-light border-0">{{ $skills->count() }}</span>
                                            </div>
                                            <div class="pt-2 px-1 skill-badges">
                                                @foreach ($skills as $skill)
                                                    <span class="badge bg-primary me-2 mb-2 py-2 px-3" style="font-size: 0.85rem;">
                                                        {{ $skill['name'] }}
                                                        <i class="bi bi-x-circle ms-1 cursor-pointer remove-skill" data-skill="{{ $skill['name'] }}" style="cursor: pointer;"></i>
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @empty
                                        <div class="alert alert-info mb-0">
                                            <i class="bi bi-info-circle me-2"></i>No skills added yet. Click "Add Skill" to get started!
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm" style="min-height: 400px;">
                            <div class="card-header border-bottom-0 bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <div class="text-white">
                                    <h5 class="mb-1 fw-bold">
                                        <i class="bi bi-lightbulb me-2"></i>Recommended Skills
                                    </h5>
                                    <small class="text-white-50">High-demand skills in your market</small>
                                </div>
                            </div>
                            <div class="card-body">
                                @forelse ($recommendedSkills as $skill)
                                    <div class="recommendation-item mb-2">
                                        <span class="badge bg-warning me-2">{{ $skill }}</span>
                                        <small class="text-muted">High demand in your area</small>
                                    </div>
                                @empty
                                    <div class="text-muted small">No skills data available from job posts yet.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-gradient border-0 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <div>
                                    <h5 class="mb-0 text-white fw-bold">Recent Applications</h5>
                                    <small class="text-white-50">Track your job applications</small>
                                </div>
                                <a href="{{ route('jobs.browse') }}" class="btn btn-light btn-sm">Browse More Jobs</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-4">Job Title</th>
                                                <th>Company</th>
                                                <th>Applied Date</th>
                                                <th>Status</th>
                                                <th>Match Score</th>
                                                <th class="pe-4">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($recentApplications as $application)
                                                @php
                                                    $required = collect($application->job?->required_skills ?? [])
                                                        ->map(fn ($skill) => Str::lower($skill))
                                                        ->filter();
                                                    $userSet = collect($userSkills)->map(fn ($skill) => Str::lower($skill));
                                                    $overlap = $required->intersect($userSet)->count();
                                                    $score = $required->count() ? (int) round(($overlap / $required->count()) * 100) : 100;
                                                    $scoreColor = $score >= 90 ? 'success' : ($score >= 80 ? 'warning' : ($score >= 70 ? 'info' : 'secondary'));
                                                    $statusBg = $application->status === 'pending' ? 'secondary' : ($application->status === 'accepted' ? 'success' : ($application->status === 'rejected' ? 'danger' : 'warning'));
                                                    $status = Str::headline($application->status ?? 'Pending');
                                                @endphp
                                                <tr class="align-middle">
                                                    <td class="ps-4 fw-semibold">{{ $application->job?->title }}</td>
                                                    <td>{{ $application->job?->company_name }}</td>
                                                    <td>
                                                        <small class="text-muted">{{ optional($application->applied_at)->format('M d, Y') }}</small>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $statusBg }} text-white">
                                                            <i class="bi bi-{{ $application->status === 'pending' ? 'hourglass-split' : ($application->status === 'accepted' ? 'check-circle' : 'x-circle') }} me-1"></i>{{ $status }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="badge bg-{{ $scoreColor }} text-white">{{ $score }}%</span>
                                                            <small class="text-muted">match</small>
                                                        </div>
                                                    </td>
                                                    <td class="pe-4">
                                                        <div class="d-flex gap-2">
                                                            <a href="{{ route('jobs.show', $application->job) }}"
                                                                class="btn btn-sm btn-primary view-application-btn"
                                                                data-application-id="{{ $application->id }}"
                                                                data-fullname="{{ e($application->full_name ?? $application->applicant?->name) }}"
                                                                data-email="{{ e($application->email ?? $application->applicant?->email) }}"
                                                                data-phone="{{ e($application->phone) }}"
                                                                data-location="{{ e($application->location) }}"
                                                                data-resume="{{ e($application->resume_path) }}"
                                                                data-cover="{{ e($application->cover_letter) }}"
                                                                data-jobtitle="{{ e($application->job?->title) }}"
                                                                data-saved-resume="{{ e($user->saved_resume_path ?? '') }}"
                                                            >
                                                                <i class="bi bi-eye me-1"></i>View
                                                            </a>
                                                            <form action="{{ route('applications.destroy', $application) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this application?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                    <i class="bi bi-trash me-1"></i>Cancel
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-5">
                                                        <i class="bi bi-inbox" style="font-size: 2rem; opacity: 0.5;"></i>
                                                        <p class="mt-3 mb-0">No applications yet. <a href="{{ route('jobs.browse') }}" class="text-primary fw-semibold">Start applying!</a></p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="employerDashboard" class="d-none">
                <div class="row mb-4">
                    <div class="col-12 col-md-3 mb-3">
                        <div class="card stat-card text-center">
                            <div class="card-body">
                                <div class="stat-icon mb-2">📋</div>
                                <h3 class="stat-number">{{ $employerStats['activeJobs'] }}</h3>
                                <p class="stat-label">Active Jobs</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 mb-3">
                        <div class="card stat-card text-center">
                            <div class="card-body">
                                <div class="stat-icon mb-2">👥</div>
                                <h3 class="stat-number">{{ $employerStats['totalApplicants'] }}</h3>
                                <p class="stat-label">Total Applicants</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 mb-3">
                        <div class="card stat-card text-center" data-stat="avg-score">
                            <div class="card-body">
                                <div class="stat-icon mb-2">⭐</div>
                                <h3 class="stat-number" id="avgScoreCount">{{ $employerStats['avgApplicantScore'] }}%</h3>
                                <p class="stat-label">Avg. Applicant Score</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 mb-3">
                        <div class="card stat-card text-center" data-stat="job-views">
                            <div class="card-body">
                                <div class="stat-icon mb-2">📈</div>
                                <h3 class="stat-number" id="jobViewsCount">{{ $employerStats['viewsCount'] }}</h3>
                                <p class="stat-label">Job Views</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Job Management</h5>
                        <a href="{{ route('employer.jobs.index') }}" class="btn btn-primary btn-sm">Post New Job</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:30%;">Job Title</th>
                                        <th style="width:25%;">Company</th>
                                        <th style="width:20%;">Posted Date</th>
                                        <th style="width:25%;">Applicants</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($employerJobs as $job)
                                        @php
                                            $applicants = $job->applications ?? collect();
                                            $appCount = $job->applications_count ?? $applicants->count();
                                            $collapseId = 'jobApplicants' . $job->id;
                                        @endphp
                                        <tr class="job-row" data-job-id="{{ $job->id }}">
                                            <td>
                                                <a href="#{{ $collapseId }}" class="text-dark fw-semibold text-decoration-none d-inline-flex align-items-center" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="{{ $collapseId }}">
                                                    {{ $job->title }}
                                                    <i class="bi bi-chevron-down ms-2 small"></i>
                                                </a>
                                                <div class="text-muted small">{{ $appCount }} Applicant{{ $appCount === 1 ? '' : 's' }}</div>
                                            </td>
                                            <td>{{ $job->company_name }}</td>
                                            <td>{{ optional($job->created_at)->format('M j, Y') }}</td>
                                            <td>
                            @php
                                $editRouteExists = Route::has('employer.jobs.edit');
                            @endphp
                                                <div class="d-flex flex-wrap gap-2">
                                                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
                                                        <i class="bi bi-people me-1"></i>View Applicants ({{ $appCount }})
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-edit-job" data-job-id="{{ $job->id }}" data-bs-toggle="modal" data-bs-target="#editJobModal">
                                                        <i class="bi bi-pencil-square me-1"></i>Edit
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="collapse" id="{{ $collapseId }}">
                                            <td colspan="4" class="p-0">
                                                <div class="p-3 bg-light border-top">
                                                    <h6 class="mb-3">
                                                        <i class="bi bi-people-fill me-2"></i>{{ $appCount }} Applicant{{ $appCount === 1 ? '' : 's' }} for {{ $job->title }}
                                                    </h6>
                                                    @forelse ($applicants as $application)
                                                        @php
                                                            $applicantName = $application->applicant?->name ?? $application->full_name ?? 'Applicant';
                                                            $initials = collect(explode(' ', $applicantName))
                                                                ->filter()
                                                                ->map(fn ($segment) => mb_substr($segment, 0, 1))
                                                                ->take(2)
                                                                ->implode('') ?: 'AP';
                                                            $email = $application->applicant?->email ?? $application->email;
                                                            $phone = $application->applicant?->phone ?? $application->phone;
                                                            $status = Str::of($application->status ?? 'Pending')->title();
                                                            $statusKey = Str::of($application->status ?? 'pending')->lower();
                                                            $badgeClass = match ($statusKey) {
                                                                'hired' => 'success',
                                                                'rejected' => 'danger',
                                                                'under review' => 'info',
                                                                'interview' => 'primary',
                                                                default => 'warning',
                                                            };
                                                            $datePrefix = $statusKey === 'hired' ? 'Hired on ' : 'Applied on ';
                                                            $dateLabel = $datePrefix . (optional($application->applied_at)->format('M j, Y') ?? '—');
                                                        @endphp
                                                        <div class="applicant-card">
                                                            <div class="card-body">
                                                                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                                                                    <div class="d-flex align-items-start">
                                                                        <div class="applicant-avatar">{{ Str::upper($initials) }}</div>
                                                                        <div class="applicant-info">
                                                                            <h6 class="mb-1">{{ $applicantName }}</h6>
                                                                            <div class="applicant-meta">
                                                                                @if ($email)
                                                                                    <div><i class="bi bi-envelope"></i>{{ $email }}</div>
                                                                                @endif
                                                                                @if ($phone)
                                                                                    <div><i class="bi bi-telephone"></i>{{ $phone }}</div>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="applicant-actions text-end">
                                                                        <span class="badge bg-{{ $badgeClass }}{{ $badgeClass === 'warning' ? ' text-dark' : '' }}">{{ $status }}</span>
                                                                        <div class="text-muted small">{{ $dateLabel }}</div>
                                                                        <div class="d-flex flex-wrap gap-1 justify-content-end">
                                                                            @if ($application->applicant_id)
                                                                                <a href="{{ route('resumes.show', $application->applicant_id) }}" class="btn btn-sm btn-outline-primary">
                                                                                    <i class="bi bi-person-lines-fill me-1"></i>Profile
                                                                                </a>
                                                                            @endif
                                                                            <button type="button" class="btn btn-sm btn-outline-secondary btn-view-app-form" data-app-id="{{ $application->id }}" data-job-id="{{ $job->id }}" data-job-title="{{ $job->title }}">
                                                                                <i class="bi bi-file-earmark-text me-1"></i>View Application
                                                                            </button>
                                                                            @if ($application->status === 'interview_scheduled')
                                                                                <button type="button" class="btn btn-sm btn-secondary" disabled>
                                                                                    <i class="bi bi-calendar-check me-1"></i>Scheduled
                                                                                </button>
                                                                            @else
                                                                                <button type="button" class="btn btn-sm btn-primary btn-schedule-interview" 
                                                                                        data-app-id="{{ $application->id }}" 
                                                                                        data-applicant-name="{{ $applicantName }}"
                                                                                        data-job-title="{{ $job->title }}">
                                                                                    <i class="bi bi-calendar-plus me-1"></i>Interview
                                                                                </button>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <div class="text-muted">No applicants yet.</div>
                                                    @endforelse
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="bi bi-briefcase" style="font-size:2rem;"></i>
                                                    <p class="mt-2 mb-0">No jobs posted yet</p>
                                                    <a href="{{ route('employer.jobs.index') }}" class="btn btn-primary btn-sm mt-2">Post Your First Job</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer py-4">
        <div class="container d-flex flex-wrap justify-content-between align-items-center">
            <p class="mb-0 text-muted">&copy; 2025 JobFilter. All rights reserved.</p>
            <div class="d-flex flex-wrap gap-3">
                <a href="#" class="text-decoration-none text-muted">Privacy</a>
                <a href="#" class="text-decoration-none text-muted">Terms</a>
                <a href="#" class="text-decoration-none text-muted">Support</a>
            </div>
        </div>
    </footer>

    <!-- Add Skill Modal -->
    <div class="modal fade" id="addSkillModal" tabindex="-1" aria-labelledby="addSkillModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="addSkillModalLabel">Add New Skill</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addSkillForm">
                        <div class="mb-3">
                            <label for="skillName" class="form-label">Skill Name</label>
                            <input type="text" class="form-control" id="skillName" placeholder="e.g., Python, React, AWS" required>
                            <small class="text-muted">Enter the skill you want to add</small>
                        </div>
                        <div class="mb-3">
                            <label for="skillCategory" class="form-label">Category (Optional)</label>
                            <select class="form-select" id="skillCategory">
                                <option value="">Select a category or leave blank for "Other"</option>
                                <option value="Backend Development">Backend Development</option>
                                <option value="Frontend Development">Frontend Development</option>
                                <option value="Data Science & Analytics">Data Science & Analytics</option>
                                <option value="Cybersecurity">Cybersecurity</option>
                                <option value="DevOps">DevOps</option>
                                <option value="Mobile Development">Mobile Development</option>
                                <option value="Cloud Computing">Cloud Computing</option>
                                <option value="Database">Database</option>
                                <option value="Development Tools">Development Tools</option>
                                <option value="Version Control">Version Control</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="addSkillBtn">Add Skill</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Job Modal -->
    <div class="modal fade" id="editJobModal" tabindex="-1" aria-labelledby="editJobModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editJobModalLabel">Edit Job Posting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editJobForm">
                        <input type="hidden" id="editJobId">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editJobTitle" class="form-label">Job Title</label>
                                <input type="text" class="form-control" id="editJobTitle" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editJobLocation" class="form-label">Location</label>
                                <input type="text" class="form-control" id="editJobLocation" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editJobType" class="form-label">Employment Type</label>
                                <select class="form-select" id="editJobType" required>
                                    <option value="">Select type...</option>
                                    <option value="Full Time">Full Time</option>
                                    <option value="Part Time">Part Time</option>
                                    <option value="Contract">Contract</option>
                                    <option value="Freelance">Freelance</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="editJobExperience" class="form-label">Experience Level</label>
                                <select class="form-select" id="editJobExperience" required>
                                    <option value="">Select level...</option>
                                    <option value="Entry Level">Entry Level</option>
                                    <option value="Mid Level">Mid Level</option>
                                    <option value="Senior Level">Senior Level</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editJobSalaryMin" class="form-label">Salary</label>
                                <input type="number" class="form-control" id="editJobSalaryMin" step="0.01">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="editJobDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editJobDescription" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="editJobResponsibilities" class="form-label">Responsibilities</label>
                            <textarea class="form-control" id="editJobResponsibilities" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="editJobRequirements" class="form-label">Requirements</label>
                            <textarea class="form-control" id="editJobRequirements" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="editJobRequiredSkills" class="form-label">Required Skills (comma-separated)</label>
                            <input type="text" class="form-control" id="editJobRequiredSkills">
                        </div>

                        <div class="mb-3">
                            <label for="editJobPreferredSkills" class="form-label">Preferred Skills (comma-separated)</label>
                            <input type="text" class="form-control" id="editJobPreferredSkills">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveJobBtn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Interview Schedule Modal -->
    <div class="modal fade" id="interviewModal" tabindex="-1" aria-labelledby="interviewModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="interviewModalLabel">Schedule Interview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="interviewForm">
                        <input type="hidden" id="applicationId" name="application_id">
                        
                        <div class="mb-3">
                            <label for="scheduledAt" class="form-label">Interview Date & Time</label>
                            <input type="datetime-local" class="form-control" id="scheduledAt" name="scheduled_at" required>
                        </div>

                        <div class="mb-3">
                            <label for="interviewType" class="form-label">Interview Type</label>
                            <select class="form-select" id="interviewType" name="interview_type" required>
                                <option value="">Select type...</option>
                                <option value="online">Online</option>
                                <option value="physical">Physical</option>
                            </select>
                        </div>

                        <div class="alert alert-info" id="letterPreview" style="display: none;">
                            <strong>Interview Letter Preview:</strong>
                            <p id="letterText" class="mt-2"></p>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="scheduleBtn">Schedule Interview</button>
                </div>
            </div>
        </div>
    </div>

        <!-- Application modal (legacy-like) -->
        <div class="modal fade" id="applicationModal" tabindex="-1" aria-labelledby="applicationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="applicationModalLabel">Apply for <span id="modalJobTitle"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="applicationPreviewForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" id="modalFullName" class="form-control" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="text" id="modalEmail" class="form-control" readonly>
                                </div>
                                <div class="col-md-6 mt-2">
                                    <label class="form-label">Phone</label>
                                    <input type="text" id="modalPhone" class="form-control" readonly>
                                </div>
                                <div class="col-md-6 mt-2">
                                    <label class="form-label">Location</label>
                                    <input type="text" id="modalLocation" class="form-control" readonly>
                                </div>
                                <div class="col-12 mt-3">
                                    <label class="form-label">Resume <small class="text-muted">(PDF, DOC, DOCX, max 5MB)</small></label>

                                    <div class="mt-2 small" id="modalCurrentResumeWrap">Current resume for this application: <a id="modalCurrentResumeLink" href="#" target="_blank">-</a></div>

                                    <div id="modalResumeArea" class="mt-2">
                                        <p class="small text-muted">No resume uploaded.</p>
                                    </div>

                                    <div class="form-check mt-3" id="useSavedResumeWrap" style="display:none;">
                                        <input class="form-check-input" type="checkbox" id="useSavedResumeCheckbox">
                                        <label class="form-check-label small" for="useSavedResumeCheckbox">Use my saved resume</label>
                                    </div>

                                    <div class="mt-2 small text-muted" id="modalSavedFilenameWrap" style="display:none;">Saved: <span id="modalSavedFilename"></span></div>

                                    <div class="mt-3" id="modalResumeFileWrap" style="display:none;">
                                        <label class="form-label small">Choose File</label>
                                        <input type="file" id="modalResumeFile" class="form-control form-control-sm" accept=".pdf,.doc,.docx">
                                    </div>

                                    <div class="form-text small text-muted mt-2">Preview is shown for PDF files only.</div>
                                </div>
                                <div class="col-12 mt-3">
                                    <label class="form-label">Cover Letter</label>
                                    <textarea id="modalCover" class="form-control" rows="5" readonly></textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="modalSubmitBtn">SUBMIT APPLICATION</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interview Scheduling Modal -->
        <div class="modal fade" id="interviewModal" tabindex="-1" aria-labelledby="interviewModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="interviewModalLabel">Schedule Interview</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="interviewScheduleForm">
                            <input type="hidden" id="interviewAppId" name="application_id">
                            <div class="mb-3">
                                <label for="interviewApplicantName" class="form-label">Applicant</label>
                                <input type="text" id="interviewApplicantName" class="form-control" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="interviewJobTitle" class="form-label">Job Position</label>
                                <input type="text" id="interviewJobTitle" class="form-control" readonly>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="interviewDate" class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="interviewDate" name="interview_date" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="interviewTime" class="form-label">Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="interviewTime" name="interview_time" required>
                                </div>
                            </div>
                            <div class="mb-3 mt-3">
                                <label class="form-label">Interview Type <span class="text-danger">*</span></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="interview_type" id="interviewTypeOnline" value="Online" required>
                                    <label class="form-check-label" for="interviewTypeOnline">
                                        Online
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="interview_type" id="interviewTypePhysical" value="Physical" required>
                                    <label class="form-check-label" for="interviewTypePhysical">
                                        Physical
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3" id="interviewLocationWrap" style="display: none;">
                                <label for="interviewLocation" class="form-label">Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="interviewLocation" name="interview_location" placeholder="Enter interview location">
                            </div>
                            <div class="alert alert-info mt-3 mb-0">
                                <strong>Preview:</strong><br>
                                <span id="interviewPreview">Hi! We would like to interview you...</span>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="interviewSubmitBtn">Schedule Interview</button>
                    </div>
                </div>
            </div>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('[data-role-switch]').forEach(btn => {
            btn.addEventListener('click', () => {
                const role = btn.getAttribute('data-role-switch');
                document.body.setAttribute('data-user-role', role);
                document.getElementById('jobSeekerDashboard').classList.toggle('d-none', role !== 'job_seeker');
                document.getElementById('employerDashboard').classList.toggle('d-none', role === 'job_seeker');
            });
        });

        const clearAllBtn = document.getElementById('notifClearAll');
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', async () => {
                try {
                    await fetch(clearAllBtn.dataset.markRead, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    });
                } catch (e) {}

                document.querySelectorAll('.notification-item').forEach(item => item.remove());
                const list = document.querySelector('.notification-dropdown');
                if (list) {
                    const empty = document.createElement('li');
                    empty.className = 'notification-item';
                    empty.innerHTML = '<div class="dropdown-item text-muted">No notifications</div>';
                    list.appendChild(empty);
                }
                const badge = document.querySelector('#notificationDropdown .badge');
                if (badge) badge.textContent = '0';
            });
        }

        if ('{{ $role }}' === 'employer') {
            document.getElementById('jobSeekerDashboard').classList.add('d-none');
            document.getElementById('employerDashboard').classList.remove('d-none');
        }

        // Application modal handling (legacy-like)
        const applicationModalEl = document.getElementById('applicationModal');
        const applicationModal = applicationModalEl ? new bootstrap.Modal(applicationModalEl) : null;

        if (applicationModalEl) {

            // Helper function to construct proper resume URL
            function getResumeUrl(resumePath) {
                if (!resumePath) return '';
                
                // If already has http, return as-is
                if (resumePath.startsWith('http')) {
                    return resumePath;
                }
                
                // Extract just the filename from any path
                let filename = resumePath;
                if (resumePath.includes('/')) {
                    const parts = resumePath.split('/');
                    filename = parts[parts.length - 1];
                }
                
                // Use the new Laravel route to serve the file
                return '/storage/resumes/' + filename;
            }

            // Helper function to update file input state based on checkbox
            function updateResumeFileState(isUsingSaved) {
                const fileInput = document.getElementById('modalResumeFile');
                const fileWrap = document.getElementById('modalResumeFileWrap');
                
                if (isUsingSaved) {
                    fileInput.disabled = true;
                    fileWrap.style.opacity = '0.5';
                    fileWrap.style.pointerEvents = 'none';
                    fileInput.value = '';
                } else {
                    fileInput.disabled = false;
                    fileWrap.style.opacity = '1';
                    fileWrap.style.pointerEvents = 'auto';
                }
            }

            // Helper function to update resume preview area
            function updateResumePreview(currentResume, savedResume) {
                const useSavedCheckbox = document.getElementById('useSavedResumeCheckbox');
                const fileInput = document.getElementById('modalResumeFile');
                const resumeArea = document.getElementById('modalResumeArea');

                if (useSavedCheckbox?.checked && savedResume) {
                    // Show saved resume preview
                    resumeArea.innerHTML = '';
                    const link = document.createElement('a');
                    const resumeUrl = getResumeUrl(savedResume);
                    link.href = resumeUrl;
                    link.target = '_blank';
                    link.textContent = 'Open saved resume';
                    link.className = 'btn btn-sm btn-outline-primary';
                    resumeArea.appendChild(link);

                    // Add PDF preview if it's a PDF
                    if (resumeUrl.toLowerCase().endsWith('.pdf')) {
                        const container = document.createElement('div');
                        container.className = 'mt-2 border position-relative';
                        container.style.minHeight = '300px';
                        container.style.backgroundColor = '#f8f9fa';
                        
                        // Try using embed tag first (often more reliable for PDFs)
                        const embed = document.createElement('embed');
                        embed.src = resumeUrl;
                        embed.type = 'application/pdf';
                        embed.style.width = '100%';
                        embed.style.height = '300px';
                        embed.style.border = 'none';
                        
                        // Add error handling
                        embed.addEventListener('error', function() {
                            container.innerHTML = `
                                <div style="padding: 20px; text-align: center;">
                                    <div style="color: #dc3545; margin-bottom: 10px;">Unable to load PDF preview</div>
                                    <a href="${resumeUrl}" target="_blank" class="btn btn-sm btn-primary">
                                        <i class="bi bi-file-pdf me-1"></i>Open PDF in New Tab
                                    </a>
                                </div>
                            `;
                        });
                        
                        container.appendChild(embed);
                        resumeArea.appendChild(container);
                    }
                } else if (fileInput?.files?.length > 0) {
                    // Show newly selected file preview
                    const file = fileInput.files[0];
                    resumeArea.innerHTML = '<p class="small text-muted">Selected: <strong>' + file.name + '</strong> (' + (file.size / 1024).toFixed(2) + ' KB)</p>';
                } else if (currentResume) {
                    // Show existing application resume
                    resumeArea.innerHTML = '';
                    const link = document.createElement('a');
                    const resumeUrl = getResumeUrl(currentResume);
                    link.href = resumeUrl;
                    link.target = '_blank';
                    link.textContent = 'Open resume';
                    link.className = 'btn btn-sm btn-outline-primary';
                    resumeArea.appendChild(link);

                    if (resumeUrl.toLowerCase().endsWith('.pdf')) {
                        const container = document.createElement('div');
                        container.className = 'mt-2 border position-relative';
                        container.style.minHeight = '300px';
                        container.style.backgroundColor = '#f8f9fa';
                        
                        // Try using embed tag first (often more reliable for PDFs)
                        const embed = document.createElement('embed');
                        embed.src = resumeUrl;
                        embed.type = 'application/pdf';
                        embed.style.width = '100%';
                        embed.style.height = '300px';
                        embed.style.border = 'none';
                        
                        // Add error handling
                        embed.addEventListener('error', function() {
                            container.innerHTML = `
                                <div style="padding: 20px; text-align: center;">
                                    <div style="color: #dc3545; margin-bottom: 10px;">Unable to load PDF preview</div>
                                    <a href="${resumeUrl}" target="_blank" class="btn btn-sm btn-primary">
                                        <i class="bi bi-file-pdf me-1"></i>Open PDF in New Tab
                                    </a>
                                </div>
                            `;
                        });
                        
                        container.appendChild(embed);
                        resumeArea.appendChild(container);
                    }
                } else {
                    resumeArea.innerHTML = '<p class="small text-muted">No resume uploaded.</p>';
                }
            }

            // Handle checkbox change
            const useSavedResumeCheckbox = document.getElementById('useSavedResumeCheckbox');
            if (useSavedResumeCheckbox) {
                useSavedResumeCheckbox.addEventListener('change', function() {
                    const savedResume = applicationModalEl.dataset.currentSavedResume || '';
                    const currentResume = applicationModalEl.dataset.currentAppResume || '';
                    updateResumeFileState(this.checked);
                    updateResumePreview(currentResume, savedResume);
                });
            }

            // Handle file input change
            const modalResumeFile = document.getElementById('modalResumeFile');
            if (modalResumeFile) {
                modalResumeFile.addEventListener('change', function() {
                    const savedResume = applicationModalEl.dataset.currentSavedResume || '';
                    const currentResume = applicationModalEl.dataset.currentAppResume || '';
                    updateResumePreview(currentResume, savedResume);
                });
            }

            // Handler for employer viewing application form
            document.querySelectorAll('.btn-view-app-form').forEach(btn => {
                btn.addEventListener('click', async function (e) {
                    e.preventDefault();
                    const appId = btn.dataset.appId || '';
                    const jobTitle = btn.dataset.jobTitle || '';
                    
                    if (!appId) {
                        alert('Missing application ID');
                        return;
                    }

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
                        const response = await fetch('/applications/' + appId, {
                            method: 'GET',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                        });

                        if (!response.ok) {
                            alert('Failed to load application');
                            return;
                        }

                        const data = await response.json();
                        const app = data.application || data;

                        // Populate modal fields
                        document.getElementById('modalJobTitle').textContent = jobTitle || app.job_title || '';
                        document.getElementById('modalFullName').value = app.full_name || app.applicant_name || '';
                        document.getElementById('modalEmail').value = app.email || app.applicant_email || '';
                        document.getElementById('modalPhone').value = app.phone || app.applicant_phone || '';
                        document.getElementById('modalLocation').value = app.location || '';
                        document.getElementById('modalCover').value = app.cover_letter || '';

                        // Update resume area
                        const resumeArea = document.getElementById('modalResumeArea');
                        resumeArea.innerHTML = '';
                        
                        if (app.resume_path) {
                            const resumeUrl = getResumeUrl(app.resume_path);
                            const link = document.createElement('a');
                            link.href = resumeUrl;
                            link.target = '_blank';
                            link.textContent = 'Open resume';
                            link.className = 'btn btn-sm btn-outline-primary';
                            resumeArea.appendChild(link);

                            if (resumeUrl.toLowerCase().endsWith('.pdf')) {
                                const container = document.createElement('div');
                                container.className = 'mt-2 border position-relative';
                                container.style.minHeight = '400px';
                                container.style.backgroundColor = '#f8f9fa';
                                
                                // Try using embed tag first (often more reliable for PDFs)
                                const embed = document.createElement('embed');
                                embed.src = resumeUrl;
                                embed.type = 'application/pdf';
                                embed.style.width = '100%';
                                embed.style.height = '400px';
                                embed.style.border = 'none';
                                
                                // Add error handling
                                embed.addEventListener('error', function() {
                                    container.innerHTML = `
                                        <div style="padding: 20px; text-align: center;">
                                            <div style="color: #dc3545; margin-bottom: 10px;">Unable to load PDF preview</div>
                                            <a href="${resumeUrl}" target="_blank" class="btn btn-sm btn-primary">
                                                <i class="bi bi-file-pdf me-1"></i>Open PDF in New Tab
                                            </a>
                                        </div>
                                    `;
                                });
                                
                                container.appendChild(embed);
                                resumeArea.appendChild(container);
                            }
                        } else {
                            resumeArea.innerHTML = '<p class="small text-muted">No resume uploaded.</p>';
                        }

                        // Set modal to read-only for employer view
                        document.getElementById('modalFullName').readOnly = true;
                        document.getElementById('modalEmail').readOnly = true;
                        document.getElementById('modalPhone').readOnly = true;
                        document.getElementById('modalLocation').readOnly = true;
                        document.getElementById('modalCover').readOnly = true;

                        // Hide edit controls for employer view
                        const useSavedWrap = document.getElementById('useSavedResumeWrap');
                        const resumeFileWrap = document.getElementById('modalResumeFileWrap');
                        const modalSubmitBtn = document.getElementById('modalSubmitBtn');
                        
                        if (useSavedWrap) useSavedWrap.style.display = 'none';
                        if (resumeFileWrap) resumeFileWrap.style.display = 'none';
                        if (modalSubmitBtn) modalSubmitBtn.style.display = 'none';

                        // Store application ID for reference
                        applicationModalEl.dataset.currentApplicationId = appId;
                        applicationModalEl.dataset.currentAppResume = app.resume_path || '';

                        applicationModal.show();
                    } catch (error) {
                        console.error('Error loading application:', error);
                        alert('Error loading application form');
                    }
                });
            });

            document.querySelectorAll('.view-application-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    // remember current application id and saved resume
                    const appId = btn.dataset.applicationId || '';
                    const savedResume = btn.dataset.savedResume || '';
                    const fullname = btn.dataset.fullname || '';
                    const email = btn.dataset.email || '';
                    const phone = btn.dataset.phone || '';
                    const location = btn.dataset.location || '';
                    const resume = btn.dataset.resume || '';
                    const cover = btn.dataset.cover || '';
                    const jobtitle = btn.dataset.jobtitle || '';

                    document.getElementById('modalJobTitle').textContent = jobtitle;
                    document.getElementById('modalFullName').value = fullname;
                    document.getElementById('modalEmail').value = email;
                    document.getElementById('modalPhone').value = phone;
                    document.getElementById('modalLocation').value = location;
                    document.getElementById('modalCover').value = cover;

                    const resumeArea = document.getElementById('modalResumeArea');
                    resumeArea.innerHTML = '';
                    // Show resume from the application if present, otherwise saved resume placeholder
                    let resumeUrl = '';
                    if (resume) resumeUrl = resume.startsWith('/') ? resume : ('/' + resume);
                    else if (savedResume) resumeUrl = savedResume.startsWith('/') ? savedResume : ('/' + savedResume);

                    if (resumeUrl) {
                        const link = document.createElement('a');
                        link.href = resumeUrl;
                        link.target = '_blank';
                        link.textContent = 'Open resume';
                        link.className = 'btn btn-sm btn-outline-primary';
                        resumeArea.appendChild(link);

                        if (resumeUrl.toLowerCase().endsWith('.pdf')) {
                            const container = document.createElement('div');
                            container.className = 'mt-2 border position-relative';
                            container.style.minHeight = '400px';
                            container.style.backgroundColor = '#f8f9fa';
                            
                            // Try using embed tag first (often more reliable for PDFs)
                            const embed = document.createElement('embed');
                            embed.src = resumeUrl;
                            embed.type = 'application/pdf';
                            embed.style.width = '100%';
                            embed.style.height = '400px';
                            embed.style.border = 'none';
                            
                            // Add error handling
                            embed.addEventListener('error', function() {
                                container.innerHTML = `
                                    <div style="padding: 20px; text-align: center;">
                                        <div style="color: #dc3545; margin-bottom: 10px;">Unable to load PDF preview</div>
                                        <a href="${resumeUrl}" target="_blank" class="btn btn-sm btn-primary">
                                            <i class="bi bi-file-pdf me-1"></i>Open PDF in New Tab
                                        </a>
                                    </div>
                                `;
                            });
                            
                            container.appendChild(embed);
                            resumeArea.appendChild(container);
                        }
                    } else {
                        resumeArea.innerHTML = '<p class="small text-muted">No resume uploaded.</p>';
                    }

                    // attach dataset for later use by submit/edit handlers
                    applicationModalEl.dataset.currentApplicationId = appId;
                    applicationModalEl.dataset.currentSavedResume = savedResume;
                    applicationModalEl.dataset.currentAppResume = resume || '';

                    // reset edit state
                    const useSavedWrap = document.getElementById('useSavedResumeWrap');
                    const resumeFileWrap = document.getElementById('modalResumeFileWrap');
                    const useSavedCheckbox = document.getElementById('useSavedResumeCheckbox');
                    const resumeFileInput = document.getElementById('modalResumeFile');
                    const editBtn = document.getElementById('modalEditBtn');
                    const coverEl = document.getElementById('modalCover');
                    
                    // Always show checkbox and file input wrap if saved resume exists
                    if (useSavedWrap) useSavedWrap.style.display = savedResume ? 'block' : 'none';
                    if (resumeFileWrap) resumeFileWrap.style.display = 'block';
                    
                    // Default checkbox: check if savedResume exists, otherwise uncheck
                    if (useSavedCheckbox) { useSavedCheckbox.checked = !!savedResume; }
                    
                    // show saved filename text
                    const savedNameEl = document.getElementById('modalSavedFilename');
                    const savedWrapEl = document.getElementById('modalSavedFilenameWrap');
                    if (savedResume && savedNameEl && savedWrapEl) {
                        const short = savedResume.split('/').pop();
                        savedNameEl.textContent = short || savedResume;
                        savedWrapEl.style.display = 'block';
                    } else if (savedWrapEl) {
                        savedWrapEl.style.display = 'none';
                    }
                    
                    // set current resume link for this application
                    const curLink = document.getElementById('modalCurrentResumeLink');
                    if (curLink) {
                        if (resume) {
                            const rurl = getResumeUrl(resume);
                            curLink.href = rurl;
                            curLink.textContent = rurl.split('/').pop() || 'resume';
                        } else {
                            curLink.href = '#';
                            curLink.textContent = '-';
                        }
                    }
                    if (resumeFileInput) { resumeFileInput.value = ''; }
                    if (editBtn) { editBtn.textContent = 'Update'; }
                    if (coverEl) { coverEl.readOnly = false; }

                    // Update file input state and preview
                    updateResumeFileState(!!savedResume && useSavedCheckbox.checked);
                    updateResumePreview(resume, savedResume);

                    applicationModal.show();
                });
            });
        }

        // Modal submit handler
        const modalSubmitBtn = document.getElementById('modalSubmitBtn');
        const useSavedResumeCheckbox = document.getElementById('useSavedResumeCheckbox');
        const modalResumeFile = document.getElementById('modalResumeFile');

        if (modalSubmitBtn) {
            modalSubmitBtn.addEventListener('click', async () => {
                const appId = applicationModalEl.dataset.currentApplicationId || '';
                if (!appId) {
                    alert('Missing application id.');
                    return;
                }

                const form = document.getElementById('applicationPreviewForm');
                const cover = document.getElementById('modalCover').value || '';

                const useSaved = document.getElementById('useSavedResumeCheckbox')?.checked || false;
                const fileInput = document.getElementById('modalResumeFile');

                // Show processing state
                const originalText = modalSubmitBtn.textContent;
                modalSubmitBtn.textContent = 'Updating...';
                modalSubmitBtn.disabled = true;

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
                    
                    const payload = {
                        cover_letter: cover,
                        use_saved_resume: useSaved ? 1 : 0,
                    };

                    if (useSaved) {
                        payload.saved_resume_path = applicationModalEl.dataset.currentSavedResume || '';
                    }

                    const resp = await fetch('/applications/' + appId, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });

                    if (resp.ok) {
                        try {
                            const data = await resp.json();
                            applicationModal.hide();
                            alert('Application updated successfully!');
                            setTimeout(() => location.reload(), 600);
                        } catch (e) {
                            applicationModal.hide();
                            alert('Application updated successfully!');
                            setTimeout(() => location.reload(), 600);
                        }
                    } else {
                        const errorText = await resp.text();
                        console.error('Update failed:', resp.status, errorText);
                        alert(`Failed to update application (${resp.status}). Check console for details.`);
                    }
                } catch (err) {
                    console.error('Fetch error:', err);
                    alert('Error while sending update: ' + err.message);
                } finally {
                    modalSubmitBtn.textContent = originalText;
                    modalSubmitBtn.disabled = false;
                }
            });
        }

        // Interview Scheduling Modal
        const interviewModalEl = document.getElementById('interviewModal');
        const interviewModal = interviewModalEl ? new bootstrap.Modal(interviewModalEl) : null;
        const interviewDateInput = document.getElementById('interviewDate');
        const interviewTimeInput = document.getElementById('interviewTime');
        const interviewTypeRadios = document.querySelectorAll('input[name="interview_type"]');
        const interviewLocationWrap = document.getElementById('interviewLocationWrap');
        const interviewLocationInput = document.getElementById('interviewLocation');
        const interviewPreview = document.getElementById('interviewPreview');
        const interviewSubmitBtn = document.getElementById('interviewSubmitBtn');

        // Update preview when inputs change
        function updateInterviewPreview() {
            const date = interviewDateInput?.value;
            const time = interviewTimeInput?.value;
            const type = document.querySelector('input[name="interview_type"]:checked')?.value;
            const location = interviewLocationInput?.value || '';

            if (date && time && type) {
                const dateObj = new Date(date + 'T' + time);
                const formattedDate = dateObj.toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
                const formattedTime = dateObj.toLocaleTimeString('en-US', { 
                    hour: 'numeric', 
                    minute: '2-digit',
                    hour12: true 
                });

                let preview = `Hi! We would like to interview you on ${formattedDate} at ${formattedTime}`;
                if (type === 'Online') {
                    preview += ' (Online).';
                } else {
                    preview += ` (Physical)${location ? ' at ' + location : ''}.`;
                }
                interviewPreview.textContent = preview;
            } else {
                interviewPreview.textContent = 'Hi! We would like to interview you...';
            }
        }

        // Show/hide location field based on interview type
        interviewTypeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'Physical') {
                    interviewLocationWrap.style.display = 'block';
                    interviewLocationInput.required = true;
                } else {
                    interviewLocationWrap.style.display = 'none';
                    interviewLocationInput.required = false;
                    interviewLocationInput.value = '';
                }
                updateInterviewPreview();
            });
        });

        if (interviewDateInput) interviewDateInput.addEventListener('change', updateInterviewPreview);
        if (interviewTimeInput) interviewTimeInput.addEventListener('change', updateInterviewPreview);
        if (interviewLocationInput) interviewLocationInput.addEventListener('input', updateInterviewPreview);

        // Handle interview button clicks
        document.querySelectorAll('.btn-schedule-interview').forEach(btn => {
            btn.addEventListener('click', function() {
                const appId = this.dataset.appId;
                const applicantName = this.dataset.applicantName;
                const jobTitle = this.dataset.jobTitle;

                document.getElementById('interviewAppId').value = appId;
                document.getElementById('interviewApplicantName').value = applicantName;
                document.getElementById('interviewJobTitle').value = jobTitle;

                // Reset form
                document.getElementById('interviewScheduleForm').reset();
                interviewLocationWrap.style.display = 'none';
                interviewLocationInput.required = false;
                updateInterviewPreview();

                interviewModal.show();
            });
        });

        // Handle interview form submission
        if (interviewSubmitBtn) {
            interviewSubmitBtn.addEventListener('click', async function() {
                const form = document.getElementById('interviewScheduleForm');
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                const appId = document.getElementById('interviewAppId').value;
                const formData = new FormData(form);
                const data = {
                    interview_date: formData.get('interview_date'),
                    interview_time: formData.get('interview_time'),
                    interview_type: formData.get('interview_type'),
                    interview_location: formData.get('interview_location') || null,
                };

                const originalText = this.textContent;
                this.textContent = 'Scheduling...';
                this.disabled = true;

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
                    const response = await fetch(`/applications/${appId}/schedule-interview`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(data),
                    });

                    if (response.ok) {
                        const result = await response.json();
                        interviewModal.hide();
                        alert('Interview scheduled successfully! The applicant has been notified.');
                        setTimeout(() => location.reload(), 500);
                    } else {
                        const error = await response.json();
                        alert('Failed to schedule interview: ' + (error.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error scheduling interview:', error);
                    alert('Error scheduling interview: ' + error.message);
                } finally {
                    this.textContent = originalText;
                    this.disabled = false;
                }
            });
        }
    </script>

    <!-- Interview Schedule JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const interviewModal = document.getElementById('interviewModal');
        const interviewForm = document.getElementById('interviewForm');
        const applicationIdInput = document.getElementById('applicationId');
        const scheduledAtInput = document.getElementById('scheduledAt');
        const interviewTypeSelect = document.getElementById('interviewType');
        const letterPreview = document.getElementById('letterPreview');
        const letterText = document.getElementById('letterText');
        const scheduleBtn = document.getElementById('scheduleBtn');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Handle interview button clicks
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-interview') || e.target.closest('.btn-interview')) {
                const button = e.target.classList.contains('btn-interview') ? e.target : e.target.closest('.btn-interview');
                const applicationId = button.dataset.applicationId;
                applicationIdInput.value = applicationId;
                scheduledAtInput.value = '';
                interviewTypeSelect.value = '';
                letterPreview.style.display = 'none';
                
                const modal = new bootstrap.Modal(interviewModal);
                modal.show();
            }
        });

        // Update letter preview
        function updateLetterPreview() {
            const scheduledAt = scheduledAtInput.value;
            const interviewType = interviewTypeSelect.value;

            if (scheduledAt && interviewType) {
                const date = new Date(scheduledAt);
                const formattedDate = date.toLocaleString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                letterText.textContent = `Hi! We would like to interview you on ${formattedDate} via ${interviewType.charAt(0).toUpperCase() + interviewType.slice(1)}.`;
                letterPreview.style.display = 'block';
            } else {
                letterPreview.style.display = 'none';
            }
        }

        scheduledAtInput.addEventListener('change', updateLetterPreview);
        interviewTypeSelect.addEventListener('change', updateLetterPreview);

        // Handle schedule button click
        scheduleBtn.addEventListener('click', async function() {
            const applicationId = applicationIdInput.value;
            const scheduledAt = scheduledAtInput.value;
            const interviewType = interviewTypeSelect.value;

            if (!applicationId || !scheduledAt || !interviewType) {
                alert('Please fill in all fields');
                return;
            }

            try {
                scheduleBtn.disabled = true;
                const originalText = scheduleBtn.innerHTML;
                scheduleBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Scheduling...';

                console.log('Sending data:', {
                    application_id: applicationId,
                    scheduled_at: scheduledAt,
                    interview_type: interviewType
                });

                const response = await fetch('{{ route("interview-schedules.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        application_id: applicationId,
                        scheduled_at: scheduledAt,
                        interview_type: interviewType
                    })
                });

                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers.get('content-type'));

                const responseText = await response.text();
                console.log('Raw response:', responseText);

                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                    throw new Error('Server returned invalid response: ' + responseText.substring(0, 200));
                }

                if (!response.ok) {
                    // Handle validation errors
                    if (data.errors) {
                        const errorMessages = Object.values(data.errors).flat().join('\n');
                        throw new Error(errorMessages);
                    }
                    throw new Error(data.error || data.message || 'Failed to schedule interview');
                }

                // Close modal
                bootstrap.Modal.getInstance(interviewModal).hide();

                // Update the button to show scheduled status
                const button = document.querySelector(`[data-application-id="${applicationId}"].btn-interview`);
                if (button) {
                    button.classList.remove('btn-primary', 'btn-interview');
                    button.classList.add('btn-secondary');
                    button.disabled = true;
                    button.innerHTML = '<i class="bi bi-calendar-check me-1"></i>Scheduled';
                }

                // Show success message
                alert('Interview scheduled successfully!');
                
                // Reload page to reflect changes
                setTimeout(() => {
                    location.reload();
                }, 1000);

            } catch (error) {
                console.error('Error:', error);
                console.error('Full error:', error);
                alert('Error scheduling interview: ' + error.message);
                scheduleBtn.disabled = false;
                scheduleBtn.innerHTML = originalText;
            }
        });
    });

    // Refresh job views count periodically and when page becomes visible
    function refreshJobViewsCount() {
        const viewsCard = document.querySelector('[data-stat="job-views"]');
        if (!viewsCard) return;

        fetch('/api/employer/total-views')
            .then(response => {
                if (!response.ok) throw new Error('Failed to fetch');
                return response.json();
            })
            .then(data => {
                const statNumber = viewsCard.querySelector('.stat-number');
                if (statNumber) {
                    const currentValue = parseInt(statNumber.textContent);
                    if (currentValue !== data.totalViews) {
                        statNumber.textContent = data.totalViews;
                        console.log('Job views updated to:', data.totalViews);
                    }
                }
            })
            .catch(error => console.error('Error refreshing job views:', error));
    }

    // Refresh average applicant score periodically
    function refreshAverageScore() {
        const scoreCard = document.querySelector('[data-stat="avg-score"]');
        if (!scoreCard) return;

        fetch('/api/employer/average-score')
            .then(response => {
                if (!response.ok) throw new Error('Failed to fetch');
                return response.json();
            })
            .then(data => {
                const statNumber = scoreCard.querySelector('.stat-number');
                if (statNumber) {
                    const currentValue = parseFloat(statNumber.textContent);
                    if (currentValue !== data.averageScore) {
                        statNumber.textContent = data.averageScore + '%';
                        console.log('Average score updated to:', data.averageScore);
                    }
                }
            })
            .catch(error => console.error('Error refreshing average score:', error));
    }

    // Refresh every 5 seconds
    setInterval(refreshJobViewsCount, 5000);
    setInterval(refreshAverageScore, 5000);

    // Also refresh when page becomes visible (tab focus)
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            console.log('Page is visible, refreshing stats');
            refreshJobViewsCount();
            refreshAverageScore();
        }
    });

    // Initial refresh on page load
    refreshJobViewsCount();
    refreshAverageScore();

    // Handle Edit Job button click
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-edit-job') || e.target.closest('.btn-edit-job')) {
            const btn = e.target.classList.contains('btn-edit-job') ? e.target : e.target.closest('.btn-edit-job');
            const jobId = btn.dataset.jobId;
            
            // Fetch job details
            fetch(`/api/jobs/${jobId}`)
                .then(response => response.json())
                .then(data => {
                    const job = data.job;
                    
                    // Populate form fields
                    document.getElementById('editJobId').value = job.id;
                    document.getElementById('editJobTitle').value = job.title;
                    document.getElementById('editJobLocation').value = job.location;
                    document.getElementById('editJobType').value = job.employment_type;
                    document.getElementById('editJobExperience').value = job.experience_level;
                    document.getElementById('editJobSalaryMin').value = job.salary || '';
                    document.getElementById('editJobDescription').value = job.description || '';
                    document.getElementById('editJobResponsibilities').value = job.responsibilities || '';
                    document.getElementById('editJobRequirements').value = job.requirements || '';
                    document.getElementById('editJobRequiredSkills').value = (job.required_skills || []).join(', ');
                    document.getElementById('editJobPreferredSkills').value = (job.preferred_skills || []).join(', ');
                })
                .catch(error => {
                    console.error('Error loading job details:', error);
                    alert('Error loading job details');
                });
        }
    });

    // Handle Save Job button click
    document.getElementById('saveJobBtn').addEventListener('click', async function() {
        const jobId = document.getElementById('editJobId').value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        const jobData = {
            title: document.getElementById('editJobTitle').value,
            location: document.getElementById('editJobLocation').value,
            employment_type: document.getElementById('editJobType').value,
            experience_level: document.getElementById('editJobExperience').value,
            salary: document.getElementById('editJobSalaryMin').value || null,
            description: document.getElementById('editJobDescription').value,
            responsibilities: document.getElementById('editJobResponsibilities').value,
            requirements: document.getElementById('editJobRequirements').value,
            required_skills: document.getElementById('editJobRequiredSkills').value.split(',').map(s => s.trim()).filter(s => s),
            preferred_skills: document.getElementById('editJobPreferredSkills').value.split(',').map(s => s.trim()).filter(s => s),
        };

        try {
            this.disabled = true;
            const originalText = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

            const response = await fetch(`/api/jobs/${jobId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(jobData)
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Failed to update job');
            }

            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('editJobModal')).hide();
            
            // Update the job row in the table
            const jobRow = document.querySelector(`[data-job-id="${jobId}"]`);
            if (jobRow) {
                const titleCell = jobRow.querySelector('a');
                if (titleCell) {
                    titleCell.textContent = jobData.title;
                }
            }

            alert('Job updated successfully!');
        } catch (error) {
            console.error('Error:', error);
            alert('Error updating job: ' + error.message);
        } finally {
            this.disabled = false;
            this.innerHTML = originalText;
        }
    });

    // Refresh interview count for job seekers
    function refreshInterviewCount() {
        const responsesCard = document.querySelector('[data-stat="responses"]');
        if (!responsesCard) return;

        fetch('/api/job-seeker/interview-count')
            .then(response => {
                if (!response.ok) throw new Error('Failed to fetch');
                return response.json();
            })
            .then(data => {
                const statNumber = responsesCard.querySelector('.stat-number');
                if (statNumber) {
                    const currentValue = parseInt(statNumber.textContent);
                    if (currentValue !== data.interviewCount) {
                        statNumber.textContent = data.interviewCount;
                        console.log('Interview count updated to:', data.interviewCount);
                    }
                }
            })
            .catch(error => console.error('Error refreshing interview count:', error));
    }

    // Refresh average match score for job seekers
    function refreshAverageMatchScore() {
        const matchScoreCard = document.querySelector('[data-stat="avg-match-score"]');
        if (!matchScoreCard) return;

        fetch('/api/job-seeker/average-match-score')
            .then(response => {
                if (!response.ok) throw new Error('Failed to fetch');
                return response.json();
            })
            .then(data => {
                const statNumber = matchScoreCard.querySelector('.stat-number');
                if (statNumber) {
                    const currentValue = parseFloat(statNumber.textContent);
                    if (currentValue !== data.averageMatchScore) {
                        statNumber.textContent = data.averageMatchScore + '%';
                        console.log('Average match score updated to:', data.averageMatchScore);
                    }
                }
            })
            .catch(error => console.error('Error refreshing average match score:', error));
    }

    // Refresh recent applications table with updated match scores
    function refreshRecentApplications() {
        const recentAppsTable = document.querySelector('table.table-hover tbody');
        if (!recentAppsTable) return;

        // Recalculate match scores for visible applications
        const rows = recentAppsTable.querySelectorAll('tr');
        rows.forEach(row => {
            const matchScoreCell = row.querySelector('td:nth-child(5)');
            if (matchScoreCell) {
                // The match score is already calculated server-side, 
                // but we can refresh it if needed by re-rendering
                console.log('Recent applications visible');
            }
        });
    }

    // Refresh recommended skills based on job market demand
    function refreshRecommendedSkills() {
        const container = document.getElementById('recommendedSkillsContainer');
        if (!container) return;

        fetch('/api/job-seeker/recommended-skills')
            .then(response => {
                if (!response.ok) throw new Error('Failed to fetch');
                return response.json();
            })
            .then(data => {
                const skills = data.recommendedSkills || [];
                
                if (skills.length === 0) {
                    container.innerHTML = '<div class="text-center py-5"><p class="text-dark mb-0"><i class="bi bi-info-circle me-2"></i>No recommendations available yet</p></div>';
                    return;
                }

                let html = '';
                skills.forEach((skill, index) => {
                    const badgeColor = skill.count >= 5 ? 'danger' : (skill.count >= 3 ? 'warning' : 'info');
                    
                    html += `
                        <div class="recommendation-item mb-2 p-3 rounded bg-white border-start border-4 border-${badgeColor}">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="badge bg-${badgeColor} text-white fw-semibold" style="font-size: 0.9rem;">${skill.name}</span>
                                <small class="text-muted fw-semibold">In ${skill.count} job${skill.count > 1 ? 's' : ''}</small>
                            </div>
                        </div>
                    `;
                });

                container.innerHTML = html;
                console.log('Recommended skills updated:', skills);
            })
            .catch(error => {
                console.error('Error refreshing recommended skills:', error);
                container.innerHTML = '<p class="text-danger text-center py-4"><i class="bi bi-exclamation-circle me-2"></i>Error loading recommendations</p>';
            });
    }

    // Refresh every 5 seconds if on job seeker dashboard
    if (document.body.dataset.userRole === 'job_seeker') {
        setInterval(refreshInterviewCount, 5000);
        setInterval(refreshAverageMatchScore, 5000);
        setInterval(refreshRecommendedSkills, 5000);
        
        // Also refresh when page becomes visible
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                console.log('Page is visible, refreshing job seeker stats');
                refreshInterviewCount();
                refreshAverageMatchScore();
                refreshRecommendedSkills();
            }
        });

        // Initial refresh on page load
        refreshInterviewCount();
        refreshAverageMatchScore();
        refreshRecommendedSkills();
    }

    // Handle Add Skill button click
    document.getElementById('addSkillBtn').addEventListener('click', async function() {
        const skillName = document.getElementById('skillName').value.trim();
        const skillCategory = document.getElementById('skillCategory').value.trim();
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        if (!skillName) {
            alert('Please enter a skill name');
            return;
        }

        try {
            this.disabled = true;
            const originalText = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adding...';

            const requestBody = {
                skill_name: skillName
            };
            
            // Only include category if user explicitly provided one
            if (skillCategory) {
                requestBody.category = skillCategory;
            }

            const response = await fetch('/api/skills', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(requestBody)
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Failed to add skill');
            }

            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('addSkillModal')).hide();
            
            // Clear form
            document.getElementById('addSkillForm').reset();

            // Refresh skills profile
            refreshSkillsProfile();
            
            // Refresh average match score
            refreshAverageMatchScore();

            // Refresh recommended skills
            refreshRecommendedSkills();

            alert('Skill added successfully!');
        } catch (error) {
            console.error('Error:', error);
            alert('Error adding skill: ' + error.message);
        } finally {
            this.disabled = false;
            this.innerHTML = originalText;
        }
    });

    // Refresh skills profile dynamically
    function refreshSkillsProfile() {
        fetch('/api/skills/by-category')
            .then(response => {
                if (!response.ok) throw new Error('Failed to fetch');
                return response.json();
            })
            .then(data => {
                const skillsContainer = document.getElementById('skillsContainer');
                if (!skillsContainer) return;

                let html = '';
                const skillsByCategory = data.skillsByCategory;

                if (Object.keys(skillsByCategory).length === 0) {
                    html = '<div class="alert alert-info mb-0"><i class="bi bi-info-circle me-2"></i>No skills added yet. Click "Add Skill" to get started!</div>';
                } else {
                    for (const [category, skills] of Object.entries(skillsByCategory)) {
                        html += `
                            <div class="mb-4 skill-category" data-category="${category}">
                                <div class="px-3 py-2 bg-light border-start border-4 border-primary rounded-lg d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-dark">${category}</span>
                                    <span class="badge bg-primary text-light border-0">${skills.length}</span>
                                </div>
                                <div class="pt-2 px-1 skill-badges">
                                    ${skills.map(skill => `
                                        <span class="badge bg-primary me-2 mb-2 py-2 px-3" style="font-size: 0.85rem;">
                                            ${skill.name}
                                            <i class="bi bi-x-circle ms-1 cursor-pointer remove-skill" data-skill="${skill.name}" style="cursor: pointer;"></i>
                                        </span>
                                    `).join('')}
                                </div>
                            </div>
                        `;
                    }
                }

                skillsContainer.innerHTML = html;
                console.log('Skills profile updated');

                // Attach remove skill listeners
                document.querySelectorAll('.remove-skill').forEach(btn => {
                    btn.addEventListener('click', removeSkill);
                });
            })
            .catch(error => console.error('Error refreshing skills profile:', error));
    }

    // Handle remove skill
    function removeSkill(e) {
        const skillName = e.target.dataset.skill;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        if (!confirm(`Remove skill "${skillName}"?`)) return;

        fetch('/api/skills', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ skill_name: skillName })
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to remove skill');
            return response.json();
        })
        .then(data => {
            refreshSkillsProfile();
            refreshAverageMatchScore();
            console.log('Skill removed:', skillName);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error removing skill: ' + error.message);
        });
    }

    // Attach remove skill listeners on page load
    document.querySelectorAll('.remove-skill').forEach(btn => {
        btn.addEventListener('click', removeSkill);
    });

    // Handle logout button
    document.getElementById('logoutBtn').addEventListener('click', function() {
        console.log('Logout button clicked');
        const modalElement = document.getElementById('logoutConfirmModal');
        if (modalElement) {
            console.log('Modal element found');
            // Use Bootstrap's native modal show method
            modalElement.classList.add('show');
            modalElement.style.display = 'block';
            modalElement.setAttribute('aria-modal', 'true');
            modalElement.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');
            
            // Add backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = 'modal-backdrop';
            document.body.appendChild(backdrop);
        } else {
            console.error('Modal element not found');
        }
    });

    // Function to close modal
    function closeLogoutModal() {
        console.log('Closing modal');
        const modalElement = document.getElementById('logoutConfirmModal');
        const backdrop = document.getElementById('modal-backdrop');
        
        if (modalElement) {
            modalElement.classList.remove('show');
            modalElement.style.display = 'none';
            modalElement.setAttribute('aria-modal', 'false');
            modalElement.setAttribute('aria-hidden', 'true');
        }
        
        if (backdrop) {
            backdrop.remove();
        }
        
        document.body.classList.remove('modal-open');
    }

    // Function to confirm logout
    function confirmLogout() {
        console.log('Logout confirmation clicked');
        closeLogoutModal();
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("logout") }}';
        form.style.display = 'none';
        
        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        
        form.appendChild(csrfInput);
        document.body.appendChild(form);
        console.log('Submitting logout form');
        form.submit();
    }

    // Function to show role switch modal
    function showRoleSwitchModal(targetRole) {
        const currentRole = '{{ $role }}';
        
        // Don't show modal if clicking current role
        if (targetRole === currentRole) {
            return;
        }

        const roleLabel = targetRole === 'job_seeker' ? 'Job Seeker' : 'Employer';
        const message = `Log in as ${roleLabel} to access`;
        
        // Set message and target role
        document.getElementById('roleSwitchMessage').textContent = message;
        document.getElementById('roleSwitchConfirmBtn').setAttribute('onclick', `redirectToLogin('${targetRole}')`);
        
        // Show modal
        const modalElement = document.getElementById('roleSwitchModal');
        modalElement.classList.add('show');
        modalElement.style.display = 'block';
        modalElement.setAttribute('aria-modal', 'true');
        modalElement.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        
        // Add backdrop
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = 'role-switch-backdrop';
        document.body.appendChild(backdrop);
    }

    // Function to close role switch modal
    function closeRoleSwitchModal() {
        const modalElement = document.getElementById('roleSwitchModal');
        const backdrop = document.getElementById('role-switch-backdrop');
        
        if (modalElement) {
            modalElement.classList.remove('show');
            modalElement.style.display = 'none';
            modalElement.setAttribute('aria-modal', 'false');
            modalElement.setAttribute('aria-hidden', 'true');
        }
        
        if (backdrop) {
            backdrop.remove();
        }
        
        document.body.classList.remove('modal-open');
    }

    // Function to redirect to login with role
    function redirectToLogin(role) {
        closeRoleSwitchModal();
        window.location.href = `{{ route('login') }}?role=${role}`;
    }
    </script>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="logoutConfirmLabel">Confirm Logout</h5>
                    <button type="button" class="btn-close btn-close-white" onclick="closeLogoutModal()" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to log out?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeLogoutModal()">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="confirmLogout()">Logout</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Role Switch Modal -->
    <div class="modal fade" id="roleSwitchModal" tabindex="-1" aria-labelledby="roleSwitchLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="roleSwitchLabel">Switch Role</h5>
                    <button type="button" class="btn-close btn-close-white" onclick="closeRoleSwitchModal()" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="roleSwitchMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="roleSwitchConfirmBtn">OK</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

