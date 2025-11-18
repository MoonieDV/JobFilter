@php
    use Illuminate\Support\Str;
    $role = $user->role ?? 'guest';
    $appliedLookup = collect($appliedJobIds ?? [])->flip();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Find Jobs - JobFilter</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('legacy/Images/log.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('legacy/css/home.css') }}" rel="stylesheet">
    <link href="{{ asset('legacy/css/jobs.css') }}" rel="stylesheet">
</head>
<body data-user-role="{{ $role }}">
    <nav class="navbar navbar-light bg-light sticky-top shadow-sm navbar-glass">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">JobFilter</a>
            <div class="d-flex align-items-center">
                @auth
                    <div class="dropdown me-3">
                        <a class="nav-link position-relative" href="#" role="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell fs-5"></i>
                            @if ($notifUnreadCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    {{ $notifUnreadCount }}
                                    <span class="visually-hidden">unread notifications</span>
                                </span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
                            <li><h6 class="dropdown-header">Notifications</h6></li>
                            <li><hr class="dropdown-divider"></li>
                            @forelse ($notifications as $notification)
                                <li class="notification-item" data-notif-id="{{ $notification->id }}">
                                    <div class="dropdown-item d-flex align-items-start justify-content-between">
                                        <div class="flex-shrink-0">
                                            <i class="bi bi-{{ $notification->type === 'danger' ? 'exclamation-circle text-danger' : ($notification->type === 'success' ? 'check-circle text-success' : 'info-circle text-primary') }}"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-2 me-2">
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
                @endauth
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
                        <h5 class="fw-bold mb-2" id="offcanvasName">{{ $user->name ?? 'Guest' }}</h5>
                        <p class="text-muted small mb-2">{{ $user->email ?? '' }}</p>
                        <p class="text-muted small mb-2">Role: {{ Str::headline($role) }}</p>
                        @auth
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm">Logout</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">Login</a>
                        @endauth
                        <hr class="mt-3">
                    </div>
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0" id="navMenuList">
                        <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link active" href="{{ route('jobs.browse') }}">Find Jobs</a></li>
                        @if ($role === 'employer' || $role === 'admin')
                            <li class="nav-item"><a class="nav-link" href="{{ route('employer.jobs.index') }}">Post Job</a></li>
                        @endif
                        <li class="nav-item"><a class="nav-link" href="{{ route('legacy.about') }}">About</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('legacy.contact') }}">Contact</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <section class="py-5" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-10">
                    <h1 class="text-center mb-4 text-white">Find Your Perfect Job Match</h1>
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <form id="jobSearchForm" method="GET">
                                <div class="row g-3">
                                    <div class="col-12 col-md-3">
                                        <input type="text" class="form-control" id="jobTitle" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Job title or keywords">
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <input type="text" class="form-control" id="location" name="location" value="{{ $filters['location'] ?? '' }}" placeholder="Location">
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <select class="form-select" id="category" name="employment_type">
                                            <option value="">All Categories</option>
                                            @foreach (['full_time' => 'Full Time', 'part_time' => 'Part Time', 'contract' => 'Contract', 'internship' => 'Internship'] as $value => $label)
                                                <option value="{{ $value }}" @selected(($filters['employment_type'] ?? '') === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <input type="text" class="form-control" id="salaryRange" name="salary" value="{{ $filters['salary'] ?? '' }}" placeholder="Salary Range">
                                    </div>
                                    <div class="col-12 col-md-1 d-grid">
                                        <button type="submit" class="btn btn-primary w-100">Search</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 col-lg-3">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Quick Filters</h5>
                        </div>
                        <div class="card-body">
                            <h6>Skills Match</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="skillMatch">
                                <label class="form-check-label" for="skillMatch">High skill match only</label>
                            </div>
                            <hr>
                            <h6>Experience</h6>
                            @foreach (['Entry Level', 'Mid Level', 'Senior Level'] as $level)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="{{ Str::slug($level) }}">
                                    <label class="form-check-label" for="{{ Str::slug($level) }}">{{ $level }}</label>
                                </div>
                            @endforeach
                            <hr>
                            <h6>Job Type</h6>
                            @foreach (['Full Time', 'Part Time', 'Remote'] as $type)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="{{ Str::slug($type) }}">
                                    <label class="form-check-label" for="{{ Str::slug($type) }}">{{ $type }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-9">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4>Job Results (<span id="jobCount">{{ $jobs->total() }}</span>)</h4>
                        <select class="form-select w-auto" id="sortBy">
                            <option value="relevance">Sort by Relevance</option>
                            <option value="date">Sort by Date</option>
                        </select>
                    </div>

                    @forelse ($jobs as $job)
                        @php
                            $skills = $job->required_skills ?? $job->preferred_skills ?? [];
                            if (empty($skills) && $job->experience_level) {
                                $skills = [$job->experience_level, $job->employment_type];
                            }
                            $alreadyApplied = $appliedLookup->has($job->id);
                        @endphp
                        <div class="card shadow-sm mb-4 job-card" data-job-id="{{ $job->id }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                    <div>
                                        <span class="badge bg-light text-primary mb-2">{{ $job->company_name }}</span>
                                        <h5 class="fw-bold">{{ $job->title }}</h5>
                                        <p class="text-muted mb-2">{{ $job->location }}</p>
                                        <div class="d-flex flex-wrap gap-3 text-muted small">
                                            <span><i class="bi bi-briefcase me-1"></i>{{ Str::headline($job->employment_type) }}</span>
                                            @if ($job->salary)
                                                <span><i class="bi bi-cash-stack me-1"></i>{{ number_format($job->salary, 2) }}</span>
                                            @endif
                                            <span><i class="bi bi-calendar me-1"></i>{{ optional($job->published_at)->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column align-items-end gap-2">
                                        @auth
                                            @if (! $alreadyApplied)
                                                <form method="POST" action="{{ route('jobs.apply', $job) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary">Apply Now</button>
                                                </form>
                                            @else
                                                <span class="badge bg-success">Applied</span>
                                            @endif
                                        @else
                                            <a href="{{ route('login') }}" class="btn btn-outline-primary">Sign in to Apply</a>
                                        @endauth
                                        <a href="{{ route('jobs.show', $job) }}" class="btn btn-outline-secondary">View Details</a>
                                    </div>
                                </div>
                                <p class="mt-3 text-muted">{{ Str::limit(strip_tags($job->description), 220) }}</p>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach (array_slice($skills, 0, 6) as $skill)
                                        <span class="badge rounded-pill bg-light text-dark">{{ $skill }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="card shadow-sm">
                            <div class="card-body text-center">
                                <h5 class="fw-bold">No jobs found</h5>
                                <p class="text-muted mb-0">Adjust your filters and try again.</p>
                            </div>
                        </div>
                    @endforelse

                    <div class="mt-4">
                        {{ $jobs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer py-4 bg-dark text-white">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-filter me-2 text-warning"></i>JobFilter
                    </h5>
                    <p class="text-muted">Revolutionizing job search with AI-powered matching technology.</p>
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-2">
                    <h6 class="fw-bold mb-3">Product</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">Features</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Pricing</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">API</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Integrations</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h6 class="fw-bold mb-3">Company</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">About</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Careers</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Blog</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h6 class="fw-bold mb-3">Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">Help Center</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Community</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Status</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Security</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h6 class="fw-bold mb-3">Legal</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">Privacy</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Terms</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Cookies</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Licenses</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">&copy; 2025 JobFilter. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 text-muted">Made with <i class="fas fa-heart text-danger"></i> for job seekers</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('legacy/js/common.js') }}"></script>
    <script src="{{ asset('legacy/js/jobs.js') }}"></script>
</body>
</html>

