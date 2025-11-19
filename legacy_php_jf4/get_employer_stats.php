<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/check_auth.php';

try {
  if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }
  $uid = (int)$_SESSION['user_id'];

  // Active jobs: count all jobs posted by this employer
  $activeJobs = 0;
  if ($s = $conn->prepare('SELECT COUNT(*) AS c FROM jobs WHERE posted_by = ?')) {
    $s->bind_param('i', $uid);
    $s->execute();
    $row = $s->get_result()->fetch_assoc();
    $activeJobs = (int)($row['c'] ?? 0);
    $s->close();
  }

  // Total applicants across all jobs posted by this employer
  $totalApplicants = 0;
  if ($s = $conn->prepare('SELECT COUNT(*) AS c FROM applications a JOIN jobs j ON j.id = a.job_id WHERE j.posted_by = ?')) {
    $s->bind_param('i', $uid);
    $s->execute();
    $row = $s->get_result()->fetch_assoc();
    $totalApplicants = (int)($row['c'] ?? 0);
    $s->close();
  }

  echo json_encode([
    'success' => true,
    'data' => [
      'active_jobs' => $activeJobs,
      'total_applicants' => $totalApplicants
    ]
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
