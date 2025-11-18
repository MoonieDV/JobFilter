@php
    use Illuminate\Support\Str;
    $role = $user->role ?? 'job_seeker';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Profile - JobFilter</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('legacy/Images/log.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('legacy/css/dashboard.css') }}" rel="stylesheet">
</head>
<body data-user-role="{{ $role }}">
    <nav class="navbar navbar-light bg-light sticky-top shadow-sm navbar-glass">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">JobFilter</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#navMenu" aria-controls="navMenu" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
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
                        <li class="nav-item"><a class="nav-link" href="{{ route('jobs.browse') }}">Find Jobs</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('employer.jobs.create') }}">Post Job</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('legacy.about') }}">About</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('legacy.contact') }}">Contact</a></li>
                        <li class="nav-item"><a class="nav-link active" href="{{ route('profile.edit') }}">Profile</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <section class="py-4 bg-primary text-white">
        <div class="container d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h1 class="fw-bold mb-1">User Profile</h1>
                <p class="mb-0">Manage your personal information and account settings.</p>
            </div>
            <div>
                <a class="btn btn-light btn-sm" href="{{ route('dashboard') }}">Back to Dashboard</a>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-12 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <img src="{{ asset('legacy/Images/icon.png') }}" class="rounded-circle" width="120" height="120" alt="Avatar">
                            </div>
                            <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                            <p class="text-muted mb-3">{{ Str::headline($role) }}</p>
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary btn-sm" type="button">Change Photo</button>
                                <a class="btn btn-outline-secondary btn-sm" href="{{ route('profile.edit') }}">User Management</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Personal Information</h5>
                            <span class="text-muted small">Profile completeness: <strong>80%</strong></span>
                        </div>
                        <div class="card-body">
                            <form id="profileForm" class="row g-3" method="POST" action="{{ route('profile.update') }}">
                                @csrf
                                @method('patch')
                                <div class="col-md-6">
                                    <label for="firstName" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="firstName" name="firstname" value="{{ old('firstname', $user->firstname) }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="lastName" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="lastName" name="lastname" value="{{ old('lastname', $user->lastname) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Display Name</label>
                                    <input type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="jobTitle" class="form-label">Job Title</label>
                                    <input type="text" class="form-control" id="jobTitle" name="job_title" value="{{ old('job_title', $user->job_title) }}">
                                </div>
                                <div class="col-12">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="address" name="company_address" value="{{ old('company_address', $user->company_address) }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="roleField" class="form-label">Role</label>
                                    <input id="roleField" class="form-control" value="{{ Str::headline($role) }}" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label for="visibility" class="form-label">Profile Visibility</label>
                                    <select id="visibility" class="form-select">
                                        <option selected>Public</option>
                                        <option>Private</option>
                                        <option>Only Employers</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="bio" class="form-label">Bio</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="3">{{ old('bio', $user->bio) }}</textarea>
                                </div>
                                <div class="col-12 d-flex justify-content-end gap-2">
                                    <button type="reset" class="btn btn-outline-secondary">Reset</button>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Security</h5>
                        </div>
                        <div class="card-body">
                            <form id="securityForm" class="row g-3" method="POST" action="{{ route('password.update') }}">
                                @csrf
                                @method('put')
                                <div class="col-md-6">
                                    <label for="currentPassword" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="newPassword" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="newPassword" name="password" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" id="confirmPassword" name="password_confirmation" required>
                                </div>
                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">Update Password</button>
                                </div>
                            </form>
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
    <script src="{{ asset('legacy/js/common.js') }}"></script>
    <script src="{{ asset('legacy/js/role-control.js') }}"></script>
</body>
</html>

