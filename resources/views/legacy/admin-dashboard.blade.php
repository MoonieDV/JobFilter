<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - JobFilter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('legacy/css/admin.css') }}">
</head>
<body class="sidebar-collapsed">
    <button id="burger-btn" aria-label="Toggle sidebar">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <nav class="sidebar" id="adminSidebar">
        <div class="sidebar-header fw-bold">JobFilter | Admin</div>
        <a href="#" class="active" data-section="dashboard"><i class="fas fa-home me-2"></i>Dashboard</a>
        <a href="#" data-section="users"><i class="fas fa-users me-2"></i>Users</a>
        <a href="#" data-section="employers"><i class="fas fa-building me-2"></i>Employers</a>
        <a href="#" data-section="jobs"><i class="fas fa-briefcase me-2"></i>Jobs</a>
        <a href="#" data-section="applications"><i class="fas fa-file-alt me-2"></i>Applications</a>
        <a href="#" data-section="feedbacks"><i class="fas fa-comments me-2"></i>Feedbacks</a>
        <a href="#" data-section="settings"><i class="fas fa-cog me-2"></i>Settings</a>
        <hr class="my-2">
        <a href="#"><i class="fas fa-user me-2"></i>Profile</a>
        <a href="#"><i class="fas fa-bell me-2"></i>Notifications <span class="badge bg-danger ms-1">3</span></a>
        <form method="POST" action="{{ route('logout') }}" class="needs-logout-confirm">
            @csrf
            <button id="logoutBtn" class="btn btn-outline-danger w-100 mt-2" type="submit"><i class="fas fa-sign-out-alt me-2"></i>Log out</button>
        </form>
    </nav>

    <main class="main-content">
        <div class="content-header py-3 px-4 bg-light shadow-sm sticky-top">
            <h2 id="section-title" class="mb-0">Dashboard</h2>
            <p class="text-muted">Manage platform activity and user data</p>
        </div>

        <div class="container-fluid py-4">
            <div id="dashboard-section">
                <div class="row g-3 mb-4">
                    <div class="col-md-3"><div class="summary-card"><h3>1200</h3><p>Total Users</p></div></div>
                    <div class="col-md-3"><div class="summary-card"><h3>350</h3><p>Total Employers</p></div></div>
                    <div class="col-md-3"><div class="summary-card"><h3>78</h3><p>Active Jobs</p></div></div>
                    <div class="col-md-3"><div class="summary-card"><h3>2150</h3><p>Applications</p></div></div>
                </div>
                <div class="row g-4 mb-4">
                    <div class="col-md-6"><div class="p-3 bg-light rounded shadow-sm"><h5>User Growth Over Time</h5><canvas id="userGrowthChart" height="200"></canvas></div></div>
                    <div class="col-md-6"><div class="p-3 bg-light rounded shadow-sm"><h5>Applications by Category</h5><canvas id="applicationsChart" height="200"></canvas></div></div>
                </div>
                <div class="row g-4 mb-4">
                    <div class="col-md-6"><div class="p-3 bg-white rounded shadow-sm"><h5>Recent User Signups</h5><ul class="list-group"><li class="list-group-item">John Doe - 2025-09-10</li><li class="list-group-item">Jane Smith - 2025-09-09</li><li class="list-group-item">Alice Johnson - 2025-09-08</li></ul></div></div>
                    <div class="col-md-6"><div class="p-3 bg-white rounded shadow-sm"><h5>Pending Employer Approvals</h5><ul class="list-group"><li class="list-group-item d-flex justify-content-between align-items-center">TechCorp Inc <span class="badge bg-warning text-dark">Pending</span></li><li class="list-group-item d-flex justify-content-between align-items-center">DesignStudio <span class="badge bg-warning text-dark">Pending</span></li></ul></div></div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="p-3 bg-secondary text-white rounded shadow-sm d-flex gap-3">
                            <button class="btn btn-light">Approve Employers</button>
                            <button class="btn btn-light">View Reports</button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="users-section" class="d-none">
                <div class="table-wrapper">
                    <h4>Users</h4>
                    <table class="table table-hover table-dark">
                        <thead>
                            <tr><th>User ID</th><th>Name</th><th>Email</th><th>Registered Date</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>U001</td><td>John Doe</td><td>john@example.com</td><td>2024-05-01</td><td>Active</td></tr>
                            <tr><td>U002</td><td>Jane Smith</td><td>jane@example.com</td><td>2024-05-03</td><td>Suspended</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="employers-section" class="d-none">
                <div class="table-wrapper">
                    <h4>Employers</h4>
                    <table class="table table-hover table-dark">
                        <thead><tr><th>Employer ID</th><th>Company Name</th><th>Industry</th><th>Joined Date</th><th>Status</th></tr></thead>
                        <tbody>
                            <tr><td>E001</td><td>ABC Corp</td><td>Technology</td><td>2023-08-15</td><td>Active</td></tr>
                            <tr><td>E002</td><td>XYZ Inc</td><td>Finance</td><td>2023-09-10</td><td>Pending</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="jobs-section" class="d-none">
                <div class="table-wrapper">
                    <h4>Jobs</h4>
                    <table class="table table-hover table-dark">
                        <thead><tr><th>Job ID</th><th>Title</th><th>Employer</th><th>Status</th><th>Posted Date</th></tr></thead>
                        <tbody>
                            <tr><td>J001</td><td>Frontend Developer</td><td>ABC Corp</td><td>Active</td><td>2025-08-10</td></tr>
                            <tr><td>J002</td><td>Data Analyst</td><td>XYZ Inc</td><td>Expired</td><td>2025-07-01</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="applications-section" class="d-none">
                <div class="table-wrapper">
                    <h4>Applications</h4>
                    <table class="table table-hover table-dark">
                        <thead><tr><th>Application ID</th><th>Job Title</th><th>Applicant Name</th><th>Status</th><th>Applied Date</th></tr></thead>
                        <tbody>
                            <tr><td>A001</td><td>Frontend Developer</td><td>John Doe</td><td>Reviewed</td><td>2025-08-11</td></tr>
                            <tr><td>A002</td><td>Data Analyst</td><td>Jane Smith</td><td>Pending</td><td>2025-07-05</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="feedbacks-section" class="d-none">
                <div class="table-wrapper">
                    <h4>User Feedbacks & Concerns</h4>
                    <table class="table table-hover table-dark">
                        <thead><tr><th>Feedback ID</th><th>User Name</th><th>Email</th><th>Message</th><th>Date</th></tr></thead>
                        <tbody>
                            <tr><td>F001</td><td>John Doe</td><td>john@example.com</td><td>Having trouble uploading my resume.</td><td>2025-09-11</td></tr>
                            <tr><td>F002</td><td>Jane Smith</td><td>jane@example.com</td><td>Can you add more job categories?</td><td>2025-09-10</td></tr>
                            <tr><td>F003</td><td>Alice Johnson</td><td>alice@example.com</td><td>My application status isn't updating.</td><td>2025-09-09</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="settings-section" class="d-none">
                <div class="table-wrapper p-3 bg-dark rounded shadow-sm text-white">
                    <h4>Settings</h4>
                    <p>Admin can configure platform settings here.</p>
                    <label for="defaultLanguage" class="form-label mt-3">Default Language</label>
                    <select id="defaultLanguage" class="form-select"><option selected>English</option><option>Spanish</option><option>French</option><option>German</option></select>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                        <label class="form-check-label" for="emailNotifications">Enable Email Notifications</label>
                    </div>
                    <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" id="maintenanceMode">
                        <label class="form-check-label fw-bold" for="maintenanceMode">Enable Maintenance Mode</label>
                    </div>
                    <button class="btn btn-primary mt-4">Save Settings</button>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('legacy/js/admin_dash.js') }}"></script>
</body>
</html>

