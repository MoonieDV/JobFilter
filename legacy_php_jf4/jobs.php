<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include authentication check
require_once 'check_auth.php';
require_once __DIR__ . '/db_connect.php';

// Require authentication to view this page
requireAuth();

// Fetch latest jobs for listing
$currentUserId = $_SESSION['user_id'] ?? null;
// Notifications (same logic as dashboard, minimal include-free)
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
    if ($s = $conn->prepare('SELECT id, title, message, type, created_at, is_read FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10')) {
      $s->bind_param('i', $currentUserId);
      $s->execute();
      $rs = $s->get_result();
      while ($row = $rs->fetch_assoc()) { $notifications[] = $row; }
      $s->close();
    }
  }
}
$appliedJobIds = [];
if ($currentUserId && is_numeric($currentUserId)) {
  // Detect which applicant column exists
  $hasApplicantCol = false; $hasUserIdCol = false;
  if ($c = $conn->query("SHOW COLUMNS FROM applications LIKE 'applicant_id'")) { $hasApplicantCol = ($c->num_rows > 0); $c->close(); }
  if ($c = $conn->query("SHOW COLUMNS FROM applications LIKE 'user_id'")) { $hasUserIdCol = ($c->num_rows > 0); $c->close(); }

  if ($hasApplicantCol && $hasUserIdCol) {
    $stmt = $conn->prepare("SELECT job_id FROM applications WHERE (applicant_id = ? OR user_id = ?) AND COALESCE(LOWER(status), 'pending') NOT IN ('cancelled','canceled','rejected','hired')");
    if ($stmt) { $stmt->bind_param('ii', $currentUserId, $currentUserId); }
  } elseif ($hasUserIdCol) {
    $stmt = $conn->prepare("SELECT job_id FROM applications WHERE user_id = ? AND COALESCE(LOWER(status), 'pending') NOT IN ('cancelled','canceled','rejected','hired')");
    if ($stmt) { $stmt->bind_param('i', $currentUserId); }
  } else {
    $stmt = $conn->prepare("SELECT job_id FROM applications WHERE applicant_id = ? AND COALESCE(LOWER(status), 'pending') NOT IN ('cancelled','canceled','rejected','hired')");
    if ($stmt) { $stmt->bind_param('i', $currentUserId); }
  }

  if (isset($stmt) && $stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) { $appliedJobIds[(int)$row['job_id']] = true; }
    $stmt->close();
  }
}
$jobs = [];
$result = $conn->query("SELECT id, title, description, company_name, location, salary, required_skills, preferred_skills, created_at FROM jobs ORDER BY created_at DESC");
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $jobs[] = $row;
  }
}

// Simple skills extraction based on keywords found in title/description
function extractSkillsFromJob(array $job): array {
  $text = strtolower(($job['title'] ?? '') . ' ' . ($job['description'] ?? ''));
  $skillKeywords = [
    'javascript' => 'JavaScript',
    'typescript' => 'TypeScript',
    'react' => 'React',
    'node' => 'Node.js',
    'vue' => 'Vue',
    'angular' => 'Angular',
    'php' => 'PHP',
    'laravel' => 'Laravel',
    'symfony' => 'Symfony',
    'python' => 'Python',
    'django' => 'Django',
    'flask' => 'Flask',
    'java' => 'Java',
    'spring' => 'Spring',
    'kotlin' => 'Kotlin',
    'swift' => 'Swift',
    'go ' => 'Go',
    'golang' => 'Go',
    'c#' => 'C#',
    '.net' => '.NET',
    'sql' => 'SQL',
    'mysql' => 'MySQL',
    'postgres' => 'PostgreSQL',
    'mongodb' => 'MongoDB',
    'aws' => 'AWS',
    'azure' => 'Azure',
    'gcp' => 'GCP',
    'docker' => 'Docker',
    'kubernetes' => 'Kubernetes',
    'html' => 'HTML',
    'css' => 'CSS',
    'ui' => 'UI Design',
    'ux' => 'UX',
    'figma' => 'Figma',
  ];

  $found = [];
  foreach ($skillKeywords as $needle => $label) {
    if (strpos($text, $needle) !== false) {
      $found[$label] = true;
    }
  }

  if (empty($found)) {
    // Fallback based on role keywords in title
    $fallbacks = [];
    if (strpos($text, 'frontend') !== false) {
      $fallbacks = ['JavaScript', 'React', 'HTML', 'CSS', 'Git'];
    } elseif (strpos($text, 'backend') !== false) {
      $fallbacks = ['Node.js', 'PHP', 'SQL', 'REST', 'Docker'];
    } elseif (strpos($text, 'full stack') !== false || strpos($text, 'full-stack') !== false) {
      $fallbacks = ['JavaScript', 'React', 'Node.js', 'SQL', 'Docker'];
    } elseif (strpos($text, 'python') !== false) {
      $fallbacks = ['Python', 'Django', 'Flask', 'SQL'];
    } elseif (strpos($text, 'java') !== false) {
      $fallbacks = ['Java', 'Spring', 'SQL', 'REST'];
    } elseif (strpos($text, 'php') !== false) {
      $fallbacks = ['PHP', 'Laravel', 'MySQL', 'REST'];
    } elseif (strpos($text, 'designer') !== false || strpos($text, 'design') !== false || strpos($text, 'graphic') !== false || strpos($text, 'ui') !== false || strpos($text, 'ux') !== false) {
      $fallbacks = ['UI Design', 'UX', 'Figma', 'Photoshop', 'Illustrator'];
    } else {
      // Generic defaults when nothing matches
      $fallbacks = ['Communication', 'Teamwork', 'Problem Solving'];
    }
    foreach ($fallbacks as $label) {
      $found[$label] = true;
    }
  }

  // Return up to 6 skills
  return array_slice(array_keys($found), 0, 6);
}

function getSkillsForDisplay(array $job): array {
  // Prefer explicit skills saved with the job
  $skillsCsv = $job['required_skills'] ?? '';
  if (is_string($skillsCsv) && trim($skillsCsv) !== '') {
    $raw = array_slice(array_map('trim', explode(',', $skillsCsv)), 0, 8);
    $dedup = [];
    foreach ($raw as $s) {
      if ($s !== '') {
        $dedup[strtolower($s)] = $s;
      }
    }
    if (!empty($dedup)) {
      return array_slice(array_values($dedup), 0, 6);
    }
  }
  // Fallback to keyword-based extraction
  return extractSkillsFromJob($job);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Find Jobs - JobFilter</title>
  <link rel="icon" type="image/svg+xml" href="Images/log.png" />
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  <link href="css/home.css" rel="stylesheet">
  <link href="css/jobs.css" rel="stylesheet">
</head>
<body data-user-role="<?php echo htmlspecialchars($_SESSION['role'] ?? 'job_seeker'); ?>">

   <!-- Navbar -->
   <nav class="navbar navbar-light bg-light sticky-top shadow-sm navbar-glass">
    <div class="container">
      <a class="navbar-brand fw-bold" href="dashboard.php">JobFilter</a>
      <div class="d-flex align-items-center">
        <!-- Notification Bell (copied from dashboard.php) -->
        <div class="dropdown me-3">
          <a class="nav-link position-relative" href="#" role="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-bell fs-5"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
              <?php echo (int)$notifUnreadCount; ?>
              <span class="visually-hidden">unread notifications</span>
            </span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
            <li><h6 class="dropdown-header">Notifications</h6></li>
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
        </ul>
        </div>
      </div>
    </div>
  </nav>

  <!-- Search Section -->
  <section class="py-5" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
          <h1 class="text-center mb-4 text-white">Find Your Perfect Job Match</h1>
          
          <!-- Search Form -->
          <div class="card shadow-sm">
            <div class="card-body p-4">
              <form id="jobSearchForm">
                <div class="row g-3">
                  <div class="col-12 col-md-3">
                    <input type="text" class="form-control" id="jobTitle" placeholder="Job title or keywords">
                  </div>
                  <div class="col-12 col-md-3">
                    <input type="text" class="form-control" id="location" placeholder="Location">
                  </div>
                  <div class="col-12 col-md-2">
                    <select class="form-select" id="category">
                      <option value="">All Categories</option>
                      <option value="technology">Technology</option>
                      <option value="marketing">Marketing</option>
                      <option value="sales">Sales</option>
                      <option value="design">Design</option>
                      <option value="finance">Finance</option>
                      <option value="healthcare">Healthcare</option>
                    </select>
                  </div>
                  <div class="col-12 col-md-2">
                    <div class="dropdown w-100">
                      <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-0">$</span>
                        <input type="text" class="form-control border-start-0 rounded-0" id="salaryRange" placeholder="Salary Range" inputmode="numeric">
                        <button class="btn btn-outline-secondary border-start-0 dropdown-toggle" type="button" id="salaryToggle" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false"></button>
                      </div>
                      <ul class="dropdown-menu w-100 salary-menu" aria-labelledby="salaryToggle" id="salaryMenu" style="min-width: 200px; border: 1px solid #e0e0e0; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                        <li><h6 class="dropdown-header" style="color: #696969; font-weight: 600; padding: 8px 16px 4px 16px; margin: 0;">Salary Range</h6></li>
                        <li><a class="dropdown-item salary-option" data-range="0-30000" href="#" style="padding: 8px 16px; color: #000;">$0 - $30,000</a></li>
                        <li><a class="dropdown-item salary-option" data-range="30000-60000" href="#" style="padding: 8px 16px; color: #000;">$30,000 - $60,000</a></li>
                        <li><a class="dropdown-item salary-option" data-range="60000-100000" href="#" style="padding: 8px 16px; color: #000;">$60,000 - $100,000</a></li>
                        <li><a class="dropdown-item salary-option" data-range="100000-150000" href="#" style="padding: 8px 16px; color: #000;">$100,000 - $150,000</a></li>
                        <li><a class="dropdown-item salary-option" data-range="150000+" href="#" style="padding: 8px 16px; color: #000;">$150,000+</a></li>
                        <li><hr class="dropdown-divider" style="margin: 4px 0; border-color: #e0e0e0;"></li>
                        <li><a class="dropdown-item" id="salaryClear" href="#" style="padding: 8px 16px; color: #000;">Clear</a></li>
                      </ul>
                    </div>
                  </div>
                  <div class="col-12 col-md-2">
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

  <!-- Job Listings -->
  <section class="py-5">
    <div class="container">
      <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-12 col-lg-3">
          <div class="card shadow-sm mb-4">
            <div class="card-header">
              <h5 class="mb-0">Quick Filters</h5>
            </div>
            <div class="card-body">
              <h6>Skills Match</h6>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="skillMatch">
                <label class="form-check-label" for="skillMatch">
                  High skill match only
                </label>
              </div>
              
              <hr>
              
              <h6>Experience</h6>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="entryLevel">
                <label class="form-check-label" for="entryLevel">
                  Entry Level
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="midLevel">
                <label class="form-check-label" for="midLevel">
                  Mid Level
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="seniorLevel">
                <label class="form-check-label" for="seniorLevel">
                  Senior Level
                </label>
              </div>
              
              <hr>
              
              <h6>Job Type</h6>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="fullTime">
                <label class="form-check-label" for="fullTime">
                  Full Time
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="partTime">
                <label class="form-check-label" for="partTime">
                  Part Time
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remote">
                <label class="form-check-label" for="remote">
                  Remote
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Job Results -->
        <div class="col-12 col-lg-9">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Job Results (<span id="jobCount"><?php echo count($jobs); ?></span>)</h4>
            <select class="form-select w-auto" id="sortBy">
              <option value="relevance">Sort by Relevance</option>
              <option value="date">Sort by Date</option>
              <option value="salary">Sort by Salary</option>
            </select>
          </div>

          <!-- Job Cards -->
          <div id="jobListings">
            <?php if (empty($jobs)): ?>
              <div class="alert alert-secondary">No jobs posted yet.</div>
            <?php else: ?>
              <?php foreach ($jobs as $job): ?>
              <div class="card shadow-sm mb-3 job-card" data-salary="<?php echo $job['salary'] !== null ? (int)$job['salary'] : 0; ?>" data-salary-max="<?php echo $job['salary'] !== null ? (int)$job['salary'] : 0; ?>">
                <div class="card-body">
                  <div class="row">
                    <div class="col-12 col-md-8">
                      <h5 class="card-title"><?php echo htmlspecialchars($job['title']); ?></h5>
                      <p class="text-muted mb-2"><?php echo htmlspecialchars($job['company_name']); ?> • <?php echo htmlspecialchars($job['location']); ?></p>
                      <div class="mb-2">
                        <?php foreach (getSkillsForDisplay($job) as $skill): ?>
                          <span class="badge bg-primary me-1"><?php echo htmlspecialchars($skill); ?></span>
                        <?php endforeach; ?>
                      </div>
                      <p class="mb-2"><?php echo htmlspecialchars(mb_strimwidth($job['description'] ?? '', 0, 160, '...')); ?></p>
                      <p class="card-text">Posted on <?php echo date('Y-m-d', strtotime($job['created_at'])); ?></p>
                    </div>
                    <div class="col-12 col-md-4 text-md-end">
                      <p class="text-success fw-bold mb-2">
                        <?php echo $job['salary'] !== null ? '$' . number_format((float)$job['salary'], 2) : 'Salary not specified'; ?>
                      </p>
                      <?php $alreadyApplied = isset($appliedJobIds[(int)$job['id']]); ?>
                      <button class="btn btn-sm apply-job-btn btn-primary"
                              data-bs-toggle="modal"
                              data-bs-target="#applicationModal"
                              data-job-id="<?php echo (int)$job['id']; ?>"
                              data-job-title="<?php echo htmlspecialchars($job['title']); ?>"
                              data-company="<?php echo htmlspecialchars($job['company_name']); ?>"
                              <?php if ($alreadyApplied): ?> data-applied="1" aria-disabled="true" style="pointer-events: none; opacity: 1;" <?php endif; ?>>
                        <?php echo $alreadyApplied ? 'Applied' : 'Apply Now'; ?>
                       </button>
                    </div>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <!-- Pagination -->
          <nav aria-label="Job listings pagination">
            <ul class="pagination justify-content-center">
              <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1">Previous</a>
              </li>
              <li class="page-item active"><a class="page-link" href="#">1</a></li>
              <li class="page-item"><a class="page-link" href="#">2</a></li>
              <li class="page-item"><a class="page-link" href="#">3</a></li>
              <li class="page-item">
                <a class="page-link" href="#">Next</a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </section>

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
  <!-- Common JavaScript -->
  <script src="js/common.js"></script>
  <!-- Role Control -->
  <script src="js/role-control.js"></script>
  
  <script src="js/jobs.js"></script>
  
  <?php include __DIR__ . '/includes/application_modal.php'; ?>
  <script src="js/job_application.js"></script>
</body>
</html>
