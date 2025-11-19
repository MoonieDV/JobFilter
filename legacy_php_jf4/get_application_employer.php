<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';

try {
  if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }
  $uid = (int)$_SESSION['user_id'];
  $appId = isset($_POST['app_id']) ? (int)$_POST['app_id'] : 0;
  if ($appId <= 0) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Invalid application id']); exit; }

  $sql = 'SELECT a.id, a.job_id, a.cover_letter, a.resume_path, a.status, a.applied_at, 
                 j.title AS job_title, j.posted_by,
                 u.name AS applicant_name, u.email AS applicant_email, u.phone AS applicant_phone
          FROM applications a 
          JOIN jobs j ON j.id = a.job_id 
          JOIN users u ON u.id = a.applicant_id
          WHERE a.id = ? LIMIT 1';
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('i', $appId);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  if (!$row) { http_response_code(404); echo json_encode(['success'=>false,'message'=>'Application not found']); exit; }
  if ((int)$row['posted_by'] !== $uid) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden']); exit; }

  echo json_encode([
    'success' => true,
    'application' => [
      'id' => (int)$row['id'],
      'job_id' => (int)$row['job_id'],
      'job_title' => (string)$row['job_title'],
      'applicant_name' => (string)($row['applicant_name'] ?? ''),
      'applicant_email' => (string)($row['applicant_email'] ?? ''),
      'applicant_phone' => (string)($row['applicant_phone'] ?? ''),
      'cover_letter' => (string)($row['cover_letter'] ?? ''),
      'resume_path' => (string)($row['resume_path'] ?? ''),
      'status' => (string)($row['status'] ?? 'pending'),
      'applied_at' => (string)($row['applied_at'] ?? '')
    ]
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
