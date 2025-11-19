@php
    use Illuminate\Support\Str;

    $role = $user->role ?? 'job_seeker';
    $skillsByCategory = collect($userSkillsWithCategories)
        ->groupBy(fn ($skill) => $skill['category'] ?? 'General')
        ->sortKeys();

    $recommendedSkills = ['TypeScript', 'AWS', 'Docker'];
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
                        <form method="POST" action="{{ route('logout') }}" class="needs-logout-confirm">
                            @csrf
                            <button class="btn btn-outline-danger btn-sm">Logout</button>
                        </form>
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
                        <button class="btn btn-light btn-sm" data-role-switch="job_seeker">Job Seeker</button>
                        <button class="btn btn-outline-light btn-sm" data-role-switch="employer">Employer</button>
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
                                <h3 class="stat-number">{{ $latestJobs->count() }}</h3>
                                <p class="stat-label">New Matches</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 mb-3">
                        <div class="card stat-card text-center">
                            <div class="card-body">
                                <div class="stat-icon mb-2">📈</div>
                                <h3 class="stat-number">100%</h3>
                                <p class="stat-label">Avg. Match Score</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 mb-3">
                        <div class="card stat-card text-center">
                            <div class="card-body">
                                <div class="stat-icon mb-2">📧</div>
                                <h3 class="stat-number">1M</h3>
                                <p class="stat-label">Responses</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12 col-lg-8 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">Your Skills Profile</h5>
                                    <small class="text-muted">Grouped by category</small>
                                </div>
                                <button class="btn btn-light btn-sm rounded-pill fw-semibold">+ Add Skill</button>
                            </div>
                            <div class="card-body">
                                <div class="skills-container" style="max-height:260px;overflow:auto;">
                                    @forelse ($skillsByCategory as $category => $skills)
                                        <div class="mb-3">
                                            <div class="px-3 py-2 bg-dark bg-opacity-25 border border-secondary rounded d-flex justify-content-between align-items-center">
                                                <span class="fw-semibold text-white">{{ $category }}</span>
                                                <span class="badge bg-secondary text-light border-0">{{ $skills->count() }}</span>
                                            </div>
                                            <div class="pt-2 px-1">
                                                @foreach ($skills as $skill)
                                                    <span class="badge bg-primary me-1 mb-1">{{ $skill['name'] }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @empty
                                        <div class="alert alert-info mb-0">No skills extracted yet.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">Recommended Skills</h5>
                            </div>
                            <div class="card-body">
                                @foreach ($recommendedSkills as $skill)
                                    <div class="recommendation-item mb-2">
                                        <span class="badge bg-warning me-2">{{ $skill }}</span>
                                        <small class="text-muted">High demand in your area</small>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Recent Applications</h5>
                                <a href="{{ route('jobs.browse') }}" class="btn btn-primary btn-sm">View All</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Job Title</th>
                                                <th>Company</th>
                                                <th>Applied Date</th>
                                                <th>Status</th>
                                                <th>Match Score</th>
                                                <th>Actions</th>
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
                                                    $status = Str::headline($application->status ?? 'Pending');
                                                @endphp
                                                <tr>
                                                    <td>{{ $application->job?->title }}</td>
                                                    <td>{{ $application->job?->company_name }}</td>
                                                    <td>{{ optional($application->applied_at)->format('Y-m-d') }}</td>
                                                    <td><span class="badge bg-secondary">{{ $status }}</span></td>
                                                    <td><span class="badge bg-{{ $scoreColor }}">{{ $score }}%</span></td>
                                                    <td>
                                                                          <a href="{{ route('jobs.show', $application->job) }}"
                                                                              class="btn btn-sm btn-outline-primary view-application-btn"
                                                                              data-application-id="{{ $application->id }}"
                                                                              data-fullname="{{ e($application->full_name ?? $application->applicant?->name) }}"
                                                                              data-email="{{ e($application->email ?? $application->applicant?->email) }}"
                                                                              data-phone="{{ e($application->phone) }}"
                                                                              data-location="{{ e($application->location) }}"
                                                                              data-resume="{{ e($application->resume_path) }}"
                                                                              data-cover="{{ e($application->cover_letter) }}"
                                                                              data-jobtitle="{{ e($application->job?->title) }}"
                                                                              data-saved-resume="{{ e($user->saved_resume_path ?? '') }}"
                                                                          >View</a>
                                                        <form action="{{ route('applications.destroy', $application) }}" method="POST" class="d-inline-block ms-2" onsubmit="return confirm('Cancel this application?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">
                                                        No applications yet. <a href="{{ route('jobs.browse') }}">Start applying!</a>
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
                        <div class="card stat-card text-center">
                            <div class="card-body">
                                <div class="stat-icon mb-2">⭐</div>
                                <h3 class="stat-number">{{ $employerStats['avgApplicantScore'] }}%</h3>
                                <p class="stat-label">Avg. Applicant Score</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 mb-3">
                        <div class="card stat-card text-center">
                            <div class="card-body">
                                <div class="stat-icon mb-2">📈</div>
                                <h3 class="stat-number">{{ $employerStats['viewsCount'] }}</h3>
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
                                                    @if ($editRouteExists)
                                                        <a href="{{ route('employer.jobs.edit', $job) }}" class="btn btn-sm btn-outline-secondary">
                                                            <i class="bi bi-pencil-square me-1"></i>Edit
                                                        </a>
                                                    @endif
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
                                                                            <button type="button" class="btn btn-sm btn-primary">
                                                                                <i class="bi bi-calendar-plus me-1"></i>Interview
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <div class="text-muted">No applicants yet.</div>
                                                    @endforelse
                                                    <div class="mt-3 text-end">
                                                        <a href="{{ route('applications.index', ['job' => $job->id]) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-box-arrow-up-right me-1"></i>View All Applications
                                                        </a>
                                                    </div>
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
                
                // If already has /storage or http, return as-is
                if (resumePath.startsWith('/storage') || resumePath.startsWith('http')) {
                    return resumePath;
                }
                
                // If it's just a filename, prepend /storage/resumes/
                if (!resumePath.includes('/')) {
                    return '/storage/resumes/' + resumePath;
                }
                
                // If it starts with resumes/, prepend /storage/
                if (resumePath.startsWith('resumes/')) {
                    return '/storage/' + resumePath;
                }
                
                // Otherwise prepend /storage/ to the path, removing leading slash if present
                return '/storage/' + (resumePath.startsWith('/') ? resumePath.slice(1) : resumePath);
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
                        
                        const iframe = document.createElement('iframe');
                        iframe.src = resumeUrl;
                        iframe.style.width = '100%';
                        iframe.style.height = '300px';
                        iframe.style.border = 'none';
                        
                        iframe.onerror = function() {
                            container.innerHTML = '<div style="padding: 20px; text-align: center; color: #dc3545;">Unable to load PDF preview. <a href="' + resumeUrl + '" target="_blank">Click here to open</a></div>';
                        };
                        
                        container.appendChild(iframe);
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
                        
                        const iframe = document.createElement('iframe');
                        iframe.src = resumeUrl;
                        iframe.style.width = '100%';
                        iframe.style.height = '300px';
                        iframe.style.border = 'none';
                        
                        iframe.onerror = function() {
                            container.innerHTML = '<div style="padding: 20px; text-align: center; color: #dc3545;">Unable to load PDF preview. <a href="' + resumeUrl + '" target="_blank">Click here to open</a></div>';
                        };
                        
                        container.appendChild(iframe);
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
                                const iframe = document.createElement('iframe');
                                iframe.src = resumeUrl;
                                iframe.style.width = '100%';
                                iframe.style.height = '400px';
                                iframe.className = 'mt-2 border';
                                resumeArea.appendChild(iframe);
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
                            const iframe = document.createElement('iframe');
                            iframe.src = resumeUrl;
                            iframe.style.width = '100%';
                            iframe.style.height = '400px';
                            iframe.className = 'mt-2 border';
                            resumeArea.appendChild(iframe);
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
    </script>
</body>
</html>

