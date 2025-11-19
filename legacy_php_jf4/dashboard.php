<?php
session_start();

// Include authentication check
require_once 'check_auth.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/skill_extractor.php';

// Require authentication to view this page
requireAuth();

// Prepare jobs for dashboards
$currentUserId = $_SESSION['user_id'] ?? null;

// Notifications for current user (employer side uses this)
$notifUnreadCount = 0;
$notifications = [];
if ($currentUserId) {
  $hasNotifTable = $conn->query("SHOW TABLES LIKE 'notifications'");
  if ($hasNotifTable && $hasNotifTable->num_rows > 0) {
    if ($s = $conn->prepare('SELECT COUNT(*) AS c FROM notifications WHERE user_id = ? AND (is_read = 0 OR is_read IS NULL)')) {
      $s->bind_param('i', $currentUserId);
      $s->execute();
      $r = $s->get_result()->fetch_assoc();
      $notifUnreadCount = (int)($r['c'] ?? 0);
      $s->close();
    }
    if ($s = $conn->prepare('SELECT id, title, message, type, created_at, is_read FROM notifications WHERE user_id = ? AND (is_read = 0 OR is_read IS NULL) ORDER BY created_at DESC LIMIT 10')) {
      $s->bind_param('i', $currentUserId);
      $s->execute();
      $rs = $s->get_result();
      while ($row = $rs->fetch_assoc()) { $notifications[] = $row; }
      $s->close();
    }
  }
}

// Get user skills for job seekers
$userSkills = [];
$userSkillsWithCategories = [];
if ($currentUserId) {
    // $currentUserId already contains the user ID from session
    $skillExtractor = new SkillExtractor($conn);
    $userSkills = $skillExtractor->getUserSkills($currentUserId, $conn);
    // Sanitize legacy/bad data: drop numeric-only or empty entries
    $userSkills = array_values(array_filter($userSkills, function($s){
      $s = trim((string)$s);
      return $s !== '' && !is_numeric($s);
    }));
    
    // Get skills with categories for enhanced display
    $userSkillsWithCategories = $skillExtractor->extractSkillsWithCategories(implode(' ', $userSkills));
}

// Employer: jobs posted by current user
$employerJobs = [];
if ($currentUserId) {
  $stmt = $conn->prepare('SELECT id, title, company_name, location, IFNULL(created_at, NOW()) AS created_at FROM jobs WHERE posted_by = ? ORDER BY created_at DESC');
  if ($stmt) {
    $stmt->bind_param('i', $currentUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
      $employerJobs[] = $row;
    }
    $stmt->close();
  }
}

// Job Seeker: latest jobs (limit 10)
$latestJobs = [];
$result = $conn->query('SELECT id, title, company_name, location, IFNULL(created_at, NOW()) AS created_at FROM jobs ORDER BY created_at DESC LIMIT 10');
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $latestJobs[] = $row;
  }
}

// Recent applications for current user
$recentApplications = [];
$applicationsCount = 0;
if ($currentUserId) {
  $stmt = $conn->prepare('SELECT a.id, a.job_id, a.applied_at, IFNULL(a.status, \'Pending\') AS app_status, j.title, j.company_name, j.required_skills
                          FROM applications a
                          JOIN jobs j ON j.id = a.job_id
                          WHERE a.applicant_id = ?
                            AND COALESCE(LOWER(a.status), \'pending\') NOT IN (\'cancelled\', \'canceled\', \'rejected\', \'hired\')
                          ORDER BY a.applied_at DESC
                          LIMIT 10');
  if ($stmt) {
    $stmt->bind_param('i', $currentUserId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
      $recentApplications[] = $row;
    }
    $stmt->close();
  }
  // Count total applications for stats
  $cnt = $conn->prepare('SELECT COUNT(*) AS c FROM applications WHERE applicant_id = ?');
  if ($cnt) {
    $cnt->bind_param('i', $currentUserId);
    $cnt->execute();
    $cRes = $cnt->get_result()->fetch_assoc();
    $applicationsCount = (int)($cRes['c'] ?? 0);
    $cnt->close();
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard - JobFilter</title>
  <link rel="icon" type="image/svg+xml" href="Images/log.png" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  <link href="css/home.css" rel="stylesheet">
  <link href="css/dashboard.css" rel="stylesheet">
</head>
<body data-user-role="<?php echo htmlspecialchars($_SESSION['role'] ?? 'job_seeker'); ?>">

  <!-- Navbar -->
  <nav class="navbar navbar-light bg-light sticky-top shadow-sm navbar-glass">
    <div class="container">
      <a class="navbar-brand fw-bold" href="login.php">JobFilter</a>
      <div class="d-flex align-items-center">
        <!-- Notification Bell -->
        <div class="dropdown me-3" data-bs-auto-close="outside">
          <a class="nav-link position-relative" href="#" role="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-bell fs-5"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
              <?php echo (int)$notifUnreadCount; ?>
              <span class="visually-hidden">unread notifications</span>
            </span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
            <li class="d-flex align-items-center justify-content-between px-3 py-2">
              <h6 class="mb-0">Notifications</h6>
              <button id="notifClearAll" class="btn btn-sm btn-outline-secondary">Clear All</button>
            </li>
            <li><hr class="dropdown-divider"></li>
            <?php if (count($notifications) === 0): ?>
              <li class="notification-item">
                <div class="dropdown-item text-muted">No notifications</div>
              </li>
            <?php else: ?>
              <?php foreach ($notifications as $n): ?>
                <li class="notification-item" data-notif-id="<?php echo (int)$n['id']; ?>">
                  <div class="dropdown-item d-flex align-items-start justify-content-between">
                    <div class="flex-shrink-0">
                      <i class="bi bi-<?php echo ($n['type'] === 'danger' ? 'exclamation-circle text-danger' : ($n['type'] === 'success' ? 'check-circle text-success' : 'info-circle text-primary')); ?>"></i>
                    </div>
                    <div class="flex-grow-1 ms-2 me-2">
                      <p class="mb-0 fw-semibold"><?php echo htmlspecialchars($n['title'] ?? 'Notification'); ?></p>
                      <small class="text-muted"><?php echo htmlspecialchars($n['message'] ?? ''); ?></small>
                    </div>
                    <button class="btn btn-sm btn-link text-muted p-0 notif-clear-btn" title="Clear" data-notif-id="<?php echo (int)$n['id']; ?>">
                      <i class="bi bi-x-lg"></i>
                    </button>
                  </div>
                </li>
              <?php endforeach; ?>
            <?php endif; ?>
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
          <img id="offcanvasAvatar" src="Images/log.png" class="rounded-circle mb-2" width="72" height="72" alt="Avatar">
          <h5 class="fw-bold mb-2" id="offcanvasName"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Guest'); ?></h5>
          <p class="text-muted small mb-2"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
          <p class="text-muted small mb-2">Role: <?php 
            $displayRole = $_SESSION['role'] ?? 'user';
            if ($displayRole === 'job_seeker') {
                echo 'Job Seeker';
            } elseif ($displayRole === 'employer') {
                echo 'Employer';
            } elseif ($displayRole === 'admin') {
                echo 'Administrator';
            } else {
                echo htmlspecialchars($displayRole);
            }
          ?></p>
          <a href="process_logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
          <hr class="mt-3">
        </div>
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0" id="navMenuList">
          <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
          <?php $role = $_SESSION['role'] ?? 'job_seeker'; ?>
          <?php if ($role === 'job_seeker' || $role === 'admin') : ?>
            <li class="nav-item"><a class="nav-link" href="jobs.php">Find Jobs</a></li>
          <?php endif; ?>
          <?php if ($role === 'employer' || $role === 'admin') : ?>
            <li class="nav-item"><a class="nav-link" href="post-job.php">Post Job</a></li>
          <?php endif; ?>
          <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
          <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
          <?php if ($role === 'admin') : ?>
            <li class="nav-item"><a class="nav-link" href="user-management.php">Users</a></li>
          <?php endif; ?>
          <?php if ($role === 'employer' || $role === 'admin') : ?>
            <li class="nav-item"><a class="nav-link" href="view_resume.php">View Resumes</a></li>
          <?php endif; ?>
          <li class="nav-item"><a class="nav-link" href="user-profile.php">Profile</a></li>
        </ul>
        </div>
      </div>
    </div>
  </nav>

  <!-- Dashboard Header -->
  <section class="py-4 bg-primary text-white">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-12 col-md-8">
          <h1 class="fw-bold mb-2">Dashboard</h1>
          <p class="mb-0">Here's your personalized dashboard with job insights and recommendations.</p>
        </div>
        <div class="col-12 col-md-4 text-md-end">
          <div class="d-flex justify-content-md-end gap-2">
            <button class="btn btn-light btn-sm" id="switchRoleBtn">Switch Role</button>
            <button class="btn btn-outline-light btn-sm" id="refreshBtn">Refresh</button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Dashboard Content -->
  <section class="py-5">
    <div class="container">
      
      <!-- Role Selector -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-center">
                <div class="btn-group" role="group">
                  <input type="radio" class="btn-check" name="userRole" id="jobSeeker" checked>
                  <label class="btn btn-outline-primary" for="jobSeeker">Job Seeker</label>
                  <input type="radio" class="btn-check" name="userRole" id="employer">
                  <label class="btn btn-outline-primary" for="employer">Employer</label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Job Seeker Dashboard -->
      <div id="jobSeekerDashboard">
        <!-- Stats Cards -->
        <div class="row mb-4">
          <div class="col-12 col-md-3 mb-3">
            <div class="card stat-card">
              <div class="card-body text-center">
                <div class="stat-icon mb-2">📊</div>
                <h3 class="stat-number" id="applicationsCount" data-server-rendered="true"><?php echo (int)$applicationsCount; ?></h3>
                <p class="stat-label">Applications</p>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-3 mb-3">
            <div class="card stat-card">
              <div class="card-body text-center">
                <div class="stat-icon mb-2">🎯</div>
                <h3 class="stat-number" id="matchesCount">8</h3>
                <p class="stat-label">Job Matches</p>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-3 mb-3">
            <div class="card stat-card">
              <div class="card-body text-center">
                <div class="stat-icon mb-2">📈</div>
                <h3 class="stat-number" id="avgMatchScore">85%</h3>
                <p class="stat-label">Avg. Match Score</p>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-3 mb-3">
            <div class="card stat-card">
              <div class="card-body text-center">
                <div class="stat-icon mb-2">📧</div>
                <h3 class="stat-number" id="responsesCount">3</h3>
                <p class="stat-label">Responses</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Skills Profile -->
        <div class="row mb-4">
          <div class="col-12 col-lg-8 mb-4">
            <div class="card">
              <div class="card-header">
                <h5 class="mb-0">Your Skills Profile</h5>
              </div>
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h6 class="mb-0 text-white-50">Grouped by category</h6>
                  <button class="btn btn-light btn-sm rounded-pill fw-semibold" onclick="showSkillUpdateModal()">+ Add Skill</button>
                </div>

                <div class="skills-container" style="max-height: 260px; overflow: auto;">
                  <?php if (!empty($userSkillsWithCategories)): ?>
                    <?php 
                      // Group skills by category and sort categories alphabetically
                      $skillsByCategory = [];
                      foreach ($userSkillsWithCategories as $skill) {
                        $category = $skill['category'];
                        if (!isset($skillsByCategory[$category])) {
                          $skillsByCategory[$category] = [];
                        }
                        $skillsByCategory[$category][] = $skill['name'];
                      }
                      ksort($skillsByCategory);
                    ?>
                    <?php foreach ($skillsByCategory as $category => $skills): ?>
                      <div class="mb-2">
                        <div class="px-3 py-2 bg-dark bg-opacity-25 border border-secondary rounded d-flex justify-content-between align-items-center">
                          <span class="fw-semibold text-white"><?php echo htmlspecialchars($category); ?></span>
                          <span class="badge bg-secondary text-light border-0"><?php echo count($skills); ?></span>
                        </div>
                        <div class="pt-2 px-1">
                          <?php foreach ($skills as $skill): ?>
                            <span class="badge bg-primary me-1 mb-1"><?php echo htmlspecialchars($skill); ?></span>
                          <?php endforeach; ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <div class="alert alert-info mb-0">
                      No skills extracted. Add your skills here.
                    </div>
                  <?php endif; ?>
                </div>

                <div class="progress my-3">
                  <?php 
                    $skillCount = count($userSkills);
                    $completionPercentage = min(100, ($skillCount * 10)); // 10% per skill, max 100%
                  ?>
                  <div class="progress-bar" role="progressbar" style="width: <?php echo $completionPercentage; ?>%">
                    Profile Complete: <?php echo $completionPercentage; ?>%
                  </div>
                </div>
                <div class="d-flex gap-2">
                  <button class="btn btn-outline-primary btn-sm" onclick="showSkillUpdateModal()">Update Skills</button>
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-lg-4 mb-4">
            <div class="card">
              <div class="card-header">
                <h5 class="mb-0">Recommended Skills</h5>
              </div>
              <div class="card-body">
                <div class="recommendation-item mb-2">
                  <span class="badge bg-warning me-2">TypeScript</span>
                  <small class="text-muted">High demand in your area</small>
                </div>
                <div class="recommendation-item mb-2">
                  <span class="badge bg-warning me-2">AWS</span>
                  <small class="text-muted">Popular with employers</small>
                </div>
                <div class="recommendation-item mb-2">
                  <span class="badge bg-warning me-2">Docker</span>
                  <small class="text-muted">Growing trend</small>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Applications -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Applications</h5>
                <a href="jobs.php" class="btn btn-primary btn-sm">View All</a>
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
                    <tbody id="applicationsTable" data-server-rendered="true">
                      <?php if (count($recentApplications) > 0): ?>
                        <?php foreach ($recentApplications as $app): ?>
                          <?php 
                            // Match score: overlap of user's skills and job required_skills
                            $reqCsv = $app['required_skills'] ?? '';
                            $reqList = array_values(array_filter(array_map('trim', explode(',', (string)$reqCsv))));
                            $userSet = array_map('strtolower', $userSkills);
                            $reqSet = array_map('strtolower', $reqList);
                            $overlap = 0;
                            foreach ($reqSet as $r) { if ($r !== '' && in_array($r, $userSet, true)) { $overlap++; } }
                            $den = max(count($reqSet), 1);
                            $score = (int) round(($overlap / $den) * 100);
                            $scoreColor = $score >= 90 ? 'success' : ($score >= 80 ? 'warning' : ($score >= 70 ? 'info' : 'secondary'));
                            $status = trim((string)($app['app_status'] ?? ''));
                            if ($status === '') { $status = 'Pending'; }
                            $statusColor = (
                              $status==='Under Review' ? 'warning' : (
                              $status==='Interview Scheduled' ? 'info' : (
                              $status==='Hired' ? 'success' : (
                              $status==='Rejected' ? 'danger' : (
                              $status==='Pending' ? 'secondary' : 'secondary'))))
                            );
                          ?>
                          <tr data-application-id="<?php echo (int)$app['id']; ?>" data-job-id="<?php echo (int)($app['job_id'] ?? 0); ?>">
                            <td><?php echo htmlspecialchars($app['title']); ?></td>
                            <td><?php echo htmlspecialchars($app['company_name']); ?></td>
                            <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($app['applied_at']))); ?></td>
                            <td>
                              <span class="badge app-status badge bg-<?php echo $statusColor; ?>"><?php echo htmlspecialchars($status); ?></span>
                            </td>
                            <td><span class="badge bg-<?php echo $scoreColor; ?>"><?php echo $score; ?>%</span></td>
                            <td>
                              <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary btn-view-application" data-app-id="<?php echo (int)$app['id']; ?>" data-job-id="<?php echo (int)($app['job_id'] ?? 0); ?>" data-job-title="<?php echo htmlspecialchars($app['title']); ?>">View</button>
                                <?php 
                                  $cancelable = in_array(strtolower($status), ['pending','under review']);
                                ?>
                                <?php if ($cancelable): ?>
                                  <button class="btn btn-sm btn-outline-danger btn-cancel-application">Cancel</button>
                                <?php endif; ?>
                              </div>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="6" class="text-center text-muted py-4">No applications yet. <a href="jobs.php">Start applying for jobs!</a></td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Employer Dashboard -->
      <div id="employerDashboard" style="display: none;">
        <!-- Stats Cards -->
        <div class="row mb-4">
          <div class="col-12 col-md-3 mb-3">
            <div class="card stat-card">
              <div class="card-body text-center">
                <div class="stat-icon mb-2">📋</div>
                <h3 class="stat-number" id="activeJobsCount">0</h3>
                <p class="stat-label">Active Jobs</p>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-3 mb-3">
            <div class="card stat-card">
              <div class="card-body text-center">
                <div class="stat-icon mb-2">👥</div>
                <h3 class="stat-number" id="totalApplicants">0</h3>
                <p class="stat-label">Total Applicants</p>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-3 mb-3">
            <div class="card stat-card">
              <div class="card-body text-center">
                <div class="stat-icon mb-2">⭐</div>
                <h3 class="stat-number" id="avgApplicantScore">78%</h3>
                <p class="stat-label">Avg. Applicant Score</p>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-3 mb-3">
            <div class="card stat-card">
              <div class="card-body text-center">
                <div class="stat-icon mb-2">📈</div>
                <h3 class="stat-number" id="viewsCount">156</h3>
                <p class="stat-label">Job Views</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Job Management -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Job Management</h5>
                <a href="post-job.php" class="btn btn-primary btn-sm">Post New Job</a>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-hover mb-0">
                    <thead class="table-light">
                      <tr>
                        <th style="width: 30%;">Job Title</th>
                        <th style="width: 25%;">Company</th>
                        <th style="width: 20%;">Posted Date</th>
                        <th style="width: 25%;">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (count($employerJobs) > 0): ?>
                        <?php foreach ($employerJobs as $job): ?>
                          <?php 
                            $jobAppCount = 0;
                            if ($c = $conn->prepare('SELECT COUNT(*) AS c FROM applications WHERE job_id = ?')) {
                              $c->bind_param('i', $job['id']);
                              $c->execute();
                              $cr = $c->get_result()->fetch_assoc();
                              $jobAppCount = (int)($cr['c'] ?? 0);
                              $c->close();
                            }
                          ?>
                          <tr class="job-row" data-job-id="<?php echo $job['id']; ?>">
                            <td>
                              <a href="#" class="text-dark fw-bold text-decoration-none" data-bs-toggle="collapse" data-bs-target="#jobApplicants<?php echo $job['id']; ?>">
                                <?php echo htmlspecialchars($job['title']); ?>
                                <i class="bi bi-chevron-down ms-1"></i>
                              </a>
                            </td>
                            <td><?php echo htmlspecialchars($job['company_name']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($job['created_at'])); ?></td>
                            <td>
                              <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="collapse" data-bs-target="#jobApplicants<?php echo $job['id']; ?>">
                                <i class="bi bi-people me-1"></i>View Applicants (<?php echo $jobAppCount; ?>)
                              </button>
                              <a href="#" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil-square me-1"></i>Edit
                              </a>
                            </td>
                          </tr>
                          <!-- Applicants Dropdown Row -->
                          <tr class="collapse" id="jobApplicants<?php echo $job['id']; ?>">
                            <td colspan="4" class="p-0">
                              <div class="p-3 bg-light border-top">
                                <?php
                                  $apps = [];
                                  if ($q = $conn->prepare('SELECT a.id, a.status, a.applied_at, u.name, u.email, u.phone FROM applications a JOIN users u ON u.id = a.applicant_id WHERE a.job_id = ? ORDER BY a.applied_at DESC')) {
                                    $q->bind_param('i', $job['id']);
                                    $q->execute();
                                    $rs = $q->get_result();
                                    while ($row = $rs->fetch_assoc()) { $apps[] = $row; }
                                    $q->close();
                                  }
                                  $appCount = count($apps);
                                ?>
                                <h6 class="mb-3">
                                  <i class="bi bi-people-fill me-2"></i><?php echo $appCount; ?> Applicants for <?php echo htmlspecialchars($job['title']); ?>
                                </h6>
                                <?php if ($appCount === 0): ?>
                                  <div class="text-muted">No applicants yet.</div>
                                <?php else: ?>
                                  <?php foreach ($apps as $a): ?>
                                    <?php
                                      $nm = trim((string)($a['name'] ?? 'Applicant'));
                                      $initials = strtoupper(substr($nm,0,1) . (strpos($nm,' ')!==false ? substr(explode(' ', $nm)[1],0,1) : ''));
                                      $st = trim((string)($a['status'] ?? 'Pending'));
                                      $stNorm = strtolower($st);
                                      $badgeClass = ($stNorm==='hired'?'success':($stNorm==='rejected'?'danger':($stNorm==='under review'?'info':'warning')));
                                      $dateLabel = ($stNorm==='hired'?'Hired on ':'Applied on ') . date('M j, Y', strtotime($a['applied_at']));
                                    ?>
                                    <div class="applicant-card">
                                      <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                          <div class="d-flex align-items-start">
                                            <div class="applicant-avatar"><?php echo htmlspecialchars($initials); ?></div>
                                            <div class="applicant-info">
                                              <h6><?php echo htmlspecialchars($nm); ?></h6>
                                              <div class="applicant-meta">
                                                <div><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($a['email'] ?? ''); ?></div>
                                                <div><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($a['phone'] ?? ''); ?></div>
                                              </div>
                                            </div>
                                          </div>
                                          <div class="applicant-actions">
                                            <span class="badge bg-<?php echo $badgeClass; ?><?php echo $badgeClass==='warning'?' text-dark':''; ?>"><?php echo htmlspecialchars($st==='Pending'?'New':($st==='Under Review'?'In Review':$st)); ?></span>
                                            <span class="text-muted small"><?php echo htmlspecialchars($dateLabel); ?></span>
                                            <button class="btn btn-sm btn-outline-primary">
                                              <i class="bi bi-person-lines-fill me-1"></i>Profile
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary btn-view-app-form" data-app-id="<?php echo (int)$a['id']; ?>" data-job-id="<?php echo (int)$job['id']; ?>" data-job-title="<?php echo htmlspecialchars($job['title']); ?>">
                                              <i class="bi bi-file-earmark-text me-1"></i>View Application Form
                                            </button>
                                            <?php if ($stNorm!=='hired'): ?>
                                              <button class="btn btn-sm btn-primary">
                                                <i class="bi bi-calendar-plus me-1"></i>Interview
                                              </button>
                                            <?php else: ?>
                                              <button class="btn btn-sm btn-success">
                                                <i class="bi bi-check-circle me-1"></i>Hired
                                              </button>
                                            <?php endif; ?>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  <?php endforeach; ?>
                                <?php endif; ?>
                                <div class="mt-3 text-end">
                                  <a href="applications.php?job_id=<?php echo $job['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>View All Applications
                                  </a>
                                </div>
                              </div>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="4" class="text-center py-4">
                            <div class="text-muted">
                              <i class="bi bi-briefcase" style="font-size: 2rem;"></i>
                              <p class="mt-2 mb-0">No jobs posted yet</p>
                              <a href="post-job.php" class="btn btn-primary btn-sm mt-2">Post Your First Job</a>
                            </div>
                          </td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Skill Update Modal -->
  <div class="modal fade" id="skillUpdateModal" tabindex="-1" aria-labelledby="skillUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="skillUpdateModalLabel">Update Your Skills</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="skillUpdateForm" enctype="multipart/form-data">
            <div class="mb-3">
              <label for="newResume" class="form-label">Upload New Resume</label>
              <input type="file" class="form-control" id="newResume" name="resume" accept=".pdf,.docx,.doc,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
              <div class="form-text">Upload a new resume to automatically extract and update your skills.</div>
            </div>
            <div class="mb-3">
              <label for="manualSkills" class="form-label">Or Add Skills Manually</label>
              <input type="text" class="form-control" id="manualSkills" name="manualSkills" placeholder="e.g., JavaScript, React, Python">
              <div class="form-text">Separate multiple skills with commas.</div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" onclick="updateSkills()">Update Skills</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
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

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <?php include __DIR__ . '/includes/application_modal.php'; ?>
  <?php include __DIR__ . '/includes/employer_application_modal.php'; ?>
  <script src="js/job_application.js"></script>
  <!-- Common JavaScript -->
  <script src="js/common.js"></script>
  
  <!-- Role Control -->
  <script src="js/role-control.js"></script>
  
  <script src="js/dashboard.js"></script>
  
  <script>
    // Toggle dashboard view based on role
    function toggleDashboard(role) {
      if (role === 'job_seeker') {
        document.getElementById('jobSeekerDashboard').style.display = 'block';
        document.getElementById('employerDashboard').style.display = 'none';
        document.querySelector('body').setAttribute('data-user-role', 'job_seeker');
      } else {
        document.getElementById('jobSeekerDashboard').style.display = 'none';
        document.getElementById('employerDashboard').style.display = 'block';
        document.querySelector('body').setAttribute('data-user-role', 'employer');
      }
      
      // Update active tab
      document.querySelectorAll('.role-tab').forEach(tab => {
        if (tab.getAttribute('onclick').includes(role)) {
          tab.classList.add('active');
        } else {
          tab.classList.remove('active');
        }
      });
    }

    // View application: open modal prefilled for editing
    document.addEventListener('click', async function(e){
      const btn = e.target.closest('.btn-view-application');
      if (!btn) return;
      const appId = btn.getAttribute('data-app-id');
      const jobId = btn.getAttribute('data-job-id');
      const jobTitle = btn.getAttribute('data-job-title') || '';
      try {
        const res = await fetch('get_application.php', { method: 'POST', body: new URLSearchParams({ app_id: appId })});
        const data = await res.json();
        if (!res.ok || !data.success) { alert(data.message || 'Failed to load application'); return; }
        // Populate modal fields
        const m = document.getElementById('applicationModal');
        if (!m) return;
        document.getElementById('appJobTitle').textContent = jobTitle || data.application.title || '';
        document.getElementById('appJobId').value = data.application.job_id;
        // Cover letter
        const cl = m.querySelector('textarea[name="cover_letter"]');
        if (cl) cl.value = data.application.cover_letter || '';
        // Prefer saved resume: disable file input to avoid accidental overwrite, show current resume link if any
        const chk = m.querySelector('#useSavedResumeChk');
        const savedInput = document.getElementById('useSavedResumeInput');
        const fileIn = document.getElementById('resume');
        if (chk && !chk.disabled) {
          chk.checked = true;
          if (savedInput) savedInput.value = '1';
          if (fileIn) { fileIn.disabled = true; fileIn.value = ''; }
        }
        const exWrap = document.getElementById('appExistingResume');
        const exLink = document.getElementById('appExistingResumeLink');
        const exPrev = document.getElementById('appExistingResumePreview');
        const exFrame = document.getElementById('appExistingResumeFrame');
        if (exWrap && exLink) {
          const p = data.application.resume_path || '';
          if (p) {
            exWrap.classList.remove('d-none');
            exLink.href = p;
            exLink.textContent = (p.split('/').pop() || 'download');
            const isPdf = p.toLowerCase().endsWith('.pdf');
            if (exPrev && exFrame) {
              if (isPdf) { exPrev.classList.remove('d-none'); exFrame.src = p; }
              else { exPrev.classList.add('d-none'); exFrame.src = ''; }
            }
          } else {
            exWrap.classList.add('d-none');
            exLink.removeAttribute('href');
            exLink.textContent = 'download';
            if (exPrev && exFrame) { exPrev.classList.add('d-none'); exFrame.src = ''; }
          }
        }
        // Show modal
        const modal = new bootstrap.Modal(m);
        modal.show();
      } catch (_) {
        alert('Network error. Please try again.');
      }
    });

    // Employer: View Application Form modal
    document.addEventListener('click', async function(e){
      const btn = e.target.closest('.btn-view-app-form');
      if (!btn) return;
      const appId = btn.getAttribute('data-app-id');
      try {
        const res = await fetch('get_application_employer.php', { method: 'POST', body: new URLSearchParams({ app_id: appId }) });
        const data = await res.json();
        if (!res.ok || !data.success) { alert(data.message || 'Failed to load application'); return; }
        const a = data.application || {};
        const m = document.getElementById('employerAppModal');
        if (!m) return;
        // Fill fields
        const setText = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v || ''; };
        setText('empAppJobTitle', a.job_title);
        setText('empApplicantName', a.applicant_name);
        setText('empApplicantEmail', a.applicant_email);
        setText('empApplicantPhone', a.applicant_phone);
        setText('empAppDate', a.applied_at ? new Date(a.applied_at).toLocaleString() : '');
        setText('empAppStatus', a.status ? a.status.charAt(0).toUpperCase() + a.status.slice(1) : '');
        const cover = document.getElementById('empAppCover'); if (cover) cover.textContent = a.cover_letter || '';
        const link = document.getElementById('empAppResumeLink'); if (link) { if (a.resume_path) { link.href = a.resume_path; link.classList.remove('disabled'); } else { link.removeAttribute('href'); link.classList.add('disabled'); } }
        const prev = document.getElementById('empAppResumePreview'); const frame = document.getElementById('empAppResumeFrame');
        if (prev && frame) {
          if (a.resume_path && a.resume_path.toLowerCase().endsWith('.pdf')) { prev.classList.remove('d-none'); frame.src = a.resume_path; }
          else { prev.classList.add('d-none'); frame.src = ''; }
        }
        // Show modal
        const modal = new bootstrap.Modal(m);
        modal.show();
      } catch (_) {
        alert('Network error. Please try again.');
      }
    });
    // Initialize dashboard based on user role
    document.addEventListener('DOMContentLoaded', function() {
      const userRole = '<?php echo $_SESSION['role'] ?? 'job_seeker'; ?>';
      if (userRole) {
        toggleDashboard(userRole);
      }

      // Initialize tooltips
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });

    // Listen for apply events from jobs.php to increment Applications stat live
    window.addEventListener('storage', function(e){
      if (e.key === 'job_applied' && e.newValue) {
        try {
          const d = JSON.parse(e.newValue);
          if (d && d.ts) {
            const cntEl = document.getElementById('applicationsCount');
            if (cntEl) {
              const current = parseInt(cntEl.textContent || '0', 10) || 0;
              cntEl.textContent = String(current + 1);
            }
          }
        } catch(_) {}
      }
    });
      // Initialize popovers
      var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
      var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
      });

      // Initialize skill tags
      document.querySelectorAll('.skill-tag').forEach(tag => {
        tag.addEventListener('click', function() {
          const skillName = this.getAttribute('data-skill-name');
          const skillId = this.getAttribute('data-skill-id');
          const skillCategory = this.getAttribute('data-skill-category');
          
          // Set values in the edit modal
          document.getElementById('skillId').value = skillId || '';
          document.getElementById('skillName').value = skillName || '';
          document.getElementById('skillCategory').value = skillCategory || 'other';
          
          // Show the modal
          const modal = new bootstrap.Modal(document.getElementById('skillModal'));
          modal.show();
        });
      });

      // Handle skill form submission
      const skillForm = document.getElementById('skillForm');
      if (skillForm) {
        skillForm.addEventListener('submit', function(e) {
          e.preventDefault();
          
          const formData = new FormData(this);
          const submitBtn = this.querySelector('button[type="submit"]');
          const originalText = submitBtn.innerHTML;
          
          // Show loading state
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
          
          fetch('update_skills.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showNotification('Skills updated successfully!', 'success');
              setTimeout(() => window.location.reload(), 1000);
            } else {
              showNotification(data.message || 'Failed to update skills', 'error');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while updating skills', 'error');
          })
          .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
          });
        });
      }

      // Initialize applicant dropdowns
      initApplicantDropdowns();
      
      // Handle window resize for responsive adjustments
      handleResize();
      window.addEventListener('resize', handleResize);
    });

    // Delegated handler for cancelling an application from Recent Applications table
    document.addEventListener('click', async function(e){
      const btn = e.target.closest('.btn-cancel-application');
      if (!btn) return;
      const row = btn.closest('tr');
      const appId = row ? row.getAttribute('data-application-id') : null;
      if (!appId) return;
      if (!confirm('Cancel this application? This cannot be undone.')) return;
      btn.disabled = true;
      try {
        const fd = new FormData();
        fd.append('application_id', appId);
        const res = await fetch('cancel_application.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (!res.ok || !data.success) {
          alert(data.message || 'Failed to cancel application.');
          btn.disabled = false;
          return;
        }
        // Remove the row with a quick fade
        row.style.transition = 'opacity 0.2s ease';
        row.style.opacity = '0';
        setTimeout(() => {
          const tbody = row.parentElement;
          row.remove();
          // If no more application rows, inject empty state
          if (tbody && tbody.querySelectorAll('tr').length === 0) {
            const empty = document.createElement('tr');
            empty.innerHTML = '<td colspan="6" class="text-center text-muted py-4">No applications yet. <a href="jobs.php">Start applying for jobs!</a></td>';
            tbody.appendChild(empty);
          }
          // Decrement Applications stat
          const cntEl = document.getElementById('applicationsCount');
          if (cntEl) {
            const current = parseInt(cntEl.textContent || '0', 10) || 0;
            cntEl.textContent = String(Math.max(0, current - 1));
          }
          // Broadcast to other tabs/pages (e.g., jobs.php) to re-enable Apply button
          try {
            const jobId = row.getAttribute('data-job-id');
            if (jobId) {
              const payload = { jobId: parseInt(jobId, 10), ts: Date.now() };
              localStorage.setItem('job_app_cancelled', JSON.stringify(payload));
              // keep a persistent marker so pages opened later can process it once
              localStorage.setItem('job_app_cancelled_last', JSON.stringify(payload));
              // Clean up the event key to allow repeated events
              setTimeout(() => localStorage.removeItem('job_app_cancelled'), 50);
            }
          } catch (_) {}
        }, 200);
      } catch (err) {
        alert('Network error while cancelling. Please try again.');
        btn.disabled = false;
      }
    });

    // Initialize applicant dropdown functionality
    function initApplicantDropdowns() {
      // Add click handler to all job title toggles
      document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
          const targetId = this.getAttribute('data-bs-target');
          const targetCollapse = document.querySelector(targetId);
          const targetApplicants = targetCollapse ? targetCollapse.querySelector('.applicants-container') : null;
          
          if (targetApplicants) {
            // Toggle the show class with a small delay to allow the collapse animation to start
            setTimeout(() => {
              targetApplicants.classList.toggle('show');
              
              // Animate applicant cards
              const cards = targetApplicants.querySelectorAll('.applicant-card');
              cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.style.opacity = '0';
                card.style.transform = 'translateY(10px)';
                
                // Trigger reflow to restart animation
                void card.offsetWidth;
                
                card.style.animation = 'fadeInUp 0.4s ease-out forwards';
              });
            }, 50);
            
            // Toggle icon
            const icon = this.querySelector('i.bi');
            if (icon) {
              icon.classList.toggle('bi-chevron-down');
              icon.classList.toggle('bi-chevron-up');
            }
          }
        });
      });

      // Handle when a collapse is shown
      document.querySelectorAll('.collapse').forEach(element => {
        element.addEventListener('shown.bs.collapse', function() {
          const targetApplicants = this.querySelector('.applicants-container');
          if (targetApplicants) {
            targetApplicants.classList.add('show');
          }
        });
        
        // Handle when a collapse is hidden
        element.addEventListener('hidden.bs.collapse', function() {
          const targetApplicants = this.querySelector('.applicants-container');
          if (targetApplicants) {
            targetApplicants.classList.remove('show');
          }
        });
      });
    }

    // Function to handle window resize for responsive adjustments
    function handleResize() {
      const cards = document.querySelectorAll('.applicant-card');
      cards.forEach(card => {
        const actions = card.querySelector('.applicant-actions');
        if (window.innerWidth < 768) {
          actions.classList.add('mobile-view');
        } else {
          actions.classList.remove('mobile-view');
        }
      });
    }
    
    function showNotification(message, type) {
      const alert = document.createElement('div');
      alert.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
      alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
      alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      `;
      
      document.body.appendChild(alert);
      
      // Auto remove after 5 seconds
      setTimeout(() => {
        if (alert.parentNode) {
          alert.remove();
        }
      }, 5000);
    }

    // Mark notifications as read when the dropdown opens
    document.addEventListener('DOMContentLoaded', function(){
      const dd = document.getElementById('notificationDropdown');
      const badge = dd ? dd.querySelector('.badge') : null;
      if (dd) {
        dd.addEventListener('show.bs.dropdown', async function(){
          try {
            const res = await fetch('notifications_mark_read.php', { method: 'POST' });
            if (badge) { badge.textContent = '0'; }
          } catch(_) {}
        });
      }
    });

    // Clear individual notification item in the dropdown (visual only) and keep dropdown open
    document.addEventListener('click', function(e){
      const btn = e.target.closest('.notif-clear-btn');
      if (!btn) return;
      e.preventDefault();
      e.stopPropagation();
      const li = btn.closest('li.notification-item');
      if (li) li.remove();
      // decrement badge count
      const badge = document.querySelector('#notificationDropdown .badge');
      if (badge) {
        const n = Math.max(0, (parseInt(badge.textContent.trim())||0) - 1);
        badge.textContent = n;
      }
    });

    // Clear all notifications: mark as read server-side, clear list, reset badge; keep dropdown open
    document.addEventListener('click', async function(e){
      const btn = e.target.closest('#notifClearAll');
      if (!btn) return;
      e.preventDefault();
      e.stopPropagation();
      try {
        const res = await fetch('notifications_mark_read.php', { method: 'POST' });
        // Regardless of response, clear UI
      } catch(_) {}
      const list = document.querySelector('.notification-dropdown');
      if (list) {
        list.querySelectorAll('li.notification-item').forEach(li => li.remove());
        const empty = document.createElement('li');
        empty.className = 'notification-item';
        empty.innerHTML = '<div class="dropdown-item text-muted">No notifications</div>';
        const divider = list.querySelector('li.dropdown-divider');
        if (divider && divider.nextSibling) {
          list.insertBefore(empty, divider.nextSibling);
        } else {
          list.appendChild(empty);
        }
      }
      const badge = document.querySelector('#notificationDropdown .badge');
      if (badge) badge.textContent = '0';
    });
  </script>
</body>
</html>
