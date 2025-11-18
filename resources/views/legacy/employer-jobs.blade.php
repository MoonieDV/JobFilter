@php
    use Illuminate\Support\Str;
    $role = auth()->user()->role ?? 'job_seeker';
    $user = auth()->user();
    
    // Get notifications
    $notifications = $user->alerts()->latest()->limit(10)->get();
    $notifUnreadCount = $notifications->where('is_read', false)->count();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Job Posts - JobFilter</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('legacy/Images/log.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('legacy/css/home.css') }}" rel="stylesheet">
    <link href="{{ asset('legacy/css/dashboard.css') }}" rel="stylesheet">
</head>
<body data-user-role="{{ $role }}">
    <nav class="navbar navbar-light bg-light sticky-top shadow-sm navbar-glass">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">JobFilter</a>
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
                        <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('jobs.browse') }}">Browse Jobs</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('applications.index') }}">Applications</a></li>
                        <li class="nav-item"><a class="nav-link active" href="{{ route('employer.jobs.index') }}">My Job Posts</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('legacy.about') }}">About</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('legacy.contact') }}">Contact</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('profile.edit') }}">Profile</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Horizontal Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
        <div class="container">
            <div class="navbar-collapse show">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('jobs.browse') }}">Browse Jobs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('applications.index') }}">Applications</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('employer.jobs.index') }}" style="text-decoration: underline;">My Job Posts</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="py-4 bg-primary text-white">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h1 class="fw-bold mb-1">My Job Posts</h1>
                </div>
                <div>
                    <a href="{{ route('employer.jobs.create') }}" class="btn btn-primary" style="background-color: #6f42c1; border-color: #6f42c1;">
                        Create Job
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 25%;">TITLE</th>
                                    <th style="width: 15%;">STATUS</th>
                                    <th style="width: 15%;">APPLICATIONS</th>
                                    <th style="width: 20%;">POSTED</th>
                                    <th style="width: 25%;">ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($jobs as $job)
                                    @php
                                        $applicationCount = $job->applications()->count();
                                        $statusClass = match($job->status) {
                                            'open' => 'success',
                                            'closed' => 'secondary',
                                            'draft' => 'warning',
                                            default => 'secondary'
                                        };
                                        $statusLabel = ucfirst($job->status);
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $job->title }}</div>
                                            <small class="text-muted">{{ $job->company_name }} • {{ $job->location }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $statusClass }}">{{ $statusLabel }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $applicationCount }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $job->created_at->format('M j, Y') }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('employer.jobs.edit', $job) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil-square me-1"></i>Edit
                                                </a>
                                                <form method="POST" action="{{ route('employer.jobs.destroy', $job) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this job?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash me-1"></i>Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-briefcase" style="font-size: 3rem;"></i>
                                                <p class="mt-3 mb-0">You have not posted any jobs yet.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if ($jobs->hasPages())
                <div class="mt-4">
                    {{ $jobs->links() }}
                </div>
            @endif
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
    <script src="{{ asset('legacy/js/common.js') }}"></script>
    <script src="{{ asset('legacy/js/role-control.js') }}"></script>
</body>
</html>

