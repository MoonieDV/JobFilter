@php
    $role = $user->role ?? 'job_seeker';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Management - JobFilter</title>
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
                        <p class="text-muted small mb-2">Role: {{ ucfirst($role) }}</p>
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
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <section class="py-4 bg-primary text-white">
        <div class="container d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h1 class="fw-bold mb-1">User Management</h1>
                <p class="mb-0">View, search, and manage user accounts.</p>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-light btn-sm" href="{{ route('profile.edit') }}">My Profile</a>
                    <form method="POST" action="{{ route('logout') }}" class="needs-logout-confirm">
                        @csrf
                        <button class="btn btn-outline-danger btn-sm" type="submit">Logout</button>
                    </form>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-4">
                            <label for="searchQuery" class="form-label">Search</label>
                            <input id="searchQuery" type="text" class="form-control" placeholder="Name, email, or role">
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="filterRole" class="form-label">Role</label>
                            <select id="filterRole" class="form-select">
                                <option value="">All</option>
                                <option>Job Seeker</option>
                                <option>Employer</option>
                                <option>Admin</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="filterStatus" class="form-label">Status</label>
                            <select id="filterStatus" class="form-select">
                                <option value="">All</option>
                                <option>Active</option>
                                <option>Suspended</option>
                                <option>Pending</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2 d-grid">
                            <button id="applyFilters" class="btn btn-primary">Apply</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Users</h5>
                    <div class="d-flex gap-2">
                        <button id="exportBtn" class="btn btn-outline-secondary btn-sm" type="button">Export</button>
                        <button id="newUserBtn" class="btn btn-primary btn-sm" type="button">New User</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTable">
                                <tr>
                                    <td>John Doe</td>
                                    <td>john@example.com</td>
                                    <td><span class="badge bg-primary">Job Seeker</span></td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td>2024-01-10</td>
                                    <td class="text-end">
                                        <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-outline-primary">View</a>
                                        <button class="btn btn-sm btn-outline-secondary" type="button">Edit</button>
                                        <button class="btn btn-sm btn-outline-danger" type="button">Suspend</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Jane Smith</td>
                                    <td>jane@company.com</td>
                                    <td><span class="badge bg-warning text-dark">Employer</span></td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td>2024-02-05</td>
                                    <td class="text-end">
                                        <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-outline-primary">View</a>
                                        <button class="btn btn-sm btn-outline-secondary" type="button">Edit</button>
                                        <button class="btn btn-sm btn-outline-danger" type="button">Suspend</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <nav class="mt-3">
                        <ul class="pagination mb-0">
                            <li class="page-item disabled"><span class="page-link">Previous</span></li>
                            <li class="page-item active"><span class="page-link">1</span></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item"><a class="page-link" href="#">Next</a></li>
                        </ul>
                    </nav>
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
    <script>
        document.getElementById('applyFilters').addEventListener('click', function () {
            alert('Filters applied (demo)');
        });
        document.getElementById('exportBtn').addEventListener('click', function () {
            alert('Exporting users (demo)');
        });
        document.getElementById('newUserBtn').addEventListener('click', function () {
            alert('Open create user modal (demo)');
        });
    </script>
</body>
</html>

