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
                                <h5 class="mb-0">Your Skills Profile</h5>
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
                                <a href="{{ route('applications.index') }}" class="btn btn-primary btn-sm">View All</a>
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
                                        <tr>
                                            <td>{{ $job->title }}</td>
                                            <td>{{ $job->company_name }}</td>
                                            <td>{{ optional($job->created_at)->format('M j, Y') }}</td>
                                            <td>
                                                <div class="d-flex flex-column gap-2">
                                                    <span class="badge bg-primary">{{ $job->applications_count }} Applicants</span>
                                                    @foreach ($job->applications->take(3) as $application)
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="applicant-avatar">{{ Str::of($application->applicant?->name)->substr(0, 2)->upper() }}</div>
                                                            <div>
                                                                <strong>{{ $application->applicant?->name }}</strong>
                                                                <div class="text-muted small">{{ optional($application->applied_at)->diffForHumans() }}</div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                    @if ($job->applications_count > 3)
                                                        <a href="{{ route('applications.index') }}?job={{ $job->id }}" class="small">View all</a>
                                                    @endif
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
    </script>
</body>
</html>

