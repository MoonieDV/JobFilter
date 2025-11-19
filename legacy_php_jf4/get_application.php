<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';
try {
  if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }
  $uid = (int)$_SESSION['user_id'];
  $appId = isset($_POST['app_id']) ? (int)$_POST['app_id'] : 0;
  if ($appId <= 0) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Invalid application id']); exit; }
  $stmt = $conn->prepare('SELECT a.id, a.job_id, a.cover_letter, a.resume_path, a.status, j.title FROM applications a JOIN jobs j ON j.id = a.job_id WHERE a.id = ? AND a.applicant_id = ? LIMIT 1');
  $stmt->bind_param('ii', $appId, $uid);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  if (!$row) { http_response_code(404); echo json_encode(['success'=>false,'message'=>'Application not found']); exit; }
  echo json_encode([
    'success' => true,
    'application' => [
      'id' => (int)$row['id'],
      'job_id' => (int)$row['job_id'],
      'title' => (string)$row['title'],
      'cover_letter' => (string)($row['cover_letter'] ?? ''),
      'resume_path' => (string)($row['resume_path'] ?? ''),
      'status' => (string)($row['status'] ?? 'pending')
    ]
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
