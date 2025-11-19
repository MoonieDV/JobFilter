<?php
session_start();
header('Content-Type: application/json');
require_once 'db_connect.php';

$resp = ['success' => false, 'message' => ''];

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Invalid request method.');
  $userId = $_SESSION['user_id'] ?? null;
  if (!$userId || !is_numeric($userId)) throw new Exception('Please log in to apply.');

  $jobId = (int)($_POST['job_id'] ?? 0);
  $coverLetter = trim($_POST['cover_letter'] ?? '');
  if ($jobId <= 0) throw new Exception('Missing job information.');
  if ($coverLetter === '') throw new Exception('Cover letter is required.');

  // Verify job exists and get employer
  $js = $conn->prepare('SELECT id, posted_by, title FROM jobs WHERE id = ? LIMIT 1');
  $js->bind_param('i', $jobId);
  $js->execute();
  $job = $js->get_result()->fetch_assoc();
  $js->close();
  if (!$job) throw new Exception('Job not found.');
  $employerId = (int)$job['posted_by'];

  // Handle resume: use saved or uploaded file
  $useSaved = isset($_POST['use_saved_resume']) && $_POST['use_saved_resume'] === '1';
  $relativePath = '';
  if ($useSaved) {
    // Fetch user's saved resume_path
    $us = $conn->prepare('SELECT resume_path FROM users WHERE id = ? LIMIT 1');
    $us->bind_param('i', $userId);
    $us->execute();
    $u = $us->get_result()->fetch_assoc();
    $us->close();
    $saved = $u ? trim((string)$u['resume_path']) : '';
    if ($saved === '') throw new Exception('No saved resume found in your profile.');
    // Optional: verify file exists on disk
    $abs = __DIR__ . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $saved);
    if (!file_exists($abs)) {
      // Still allow using the path, but warn
      // throw new Exception('Saved resume file is missing on the server. Please upload a new one.');
    }
    $relativePath = $saved;
  } else {
    if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
      throw new Exception('Please attach your resume.');
    }
    $file = $_FILES['resume'];
    $max = 5 * 1024 * 1024;
    if ($file['size'] > $max) throw new Exception('File too large. Max 5MB.');
    $allowed = [
      'application/pdf' => 'pdf',
      'application/msword' => 'doc',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx'
    ];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $mime = function_exists('mime_content_type') ? mime_content_type($file['tmp_name']) : $file['type'];
    if (!in_array($ext, ['pdf','doc','docx']) && !isset($allowed[$mime])) throw new Exception('Unsupported file type. Use PDF, DOC, or DOCX.');
    $finalExt = isset($allowed[$mime]) ? $allowed[$mime] : $ext;

    $dir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'resumes';
    if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
    $filename = 'resume_' . (int)$userId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $finalExt;
    $dest = $dir . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($file['tmp_name'], $dest)) throw new Exception('Failed to save resume.');
    $relativePath = 'uploads/resumes/' . $filename;
  }

  // Detect which columns exist on applications table
  $hasApplicantCol = false; $hasUserIdCol = false;
  if ($c = $conn->query("SHOW COLUMNS FROM applications LIKE 'applicant_id'")) { $hasApplicantCol = ($c->num_rows > 0); $c->close(); }
  if ($c = $conn->query("SHOW COLUMNS FROM applications LIKE 'user_id'")) { $hasUserIdCol = ($c->num_rows > 0); $c->close(); }

  // Prevent duplicates (build query based on available columns)
  if ($hasApplicantCol && $hasUserIdCol) {
    $check = $conn->prepare('SELECT id FROM applications WHERE job_id = ? AND (applicant_id = ? OR user_id = ?) LIMIT 1');
    $check->bind_param('iii', $jobId, $userId, $userId);
  } elseif ($hasUserIdCol) {
    $check = $conn->prepare('SELECT id FROM applications WHERE job_id = ? AND user_id = ? LIMIT 1');
    $check->bind_param('ii', $jobId, $userId);
  } else {
    // Fallback to applicant_id only
    $check = $conn->prepare('SELECT id FROM applications WHERE job_id = ? AND applicant_id = ? LIMIT 1');
    $check->bind_param('ii', $jobId, $userId);
  }
  $check->execute();
  $exists = $check->get_result()->fetch_assoc();
  $check->close();

  $conn->begin_transaction();

  if ($exists) {
    // Update existing with latest cover letter and resume
    $upd = $conn->prepare('UPDATE applications SET cover_letter = ?, resume_path = ?, status = status, applied_at = applied_at WHERE id = ?');
    $appId = (int)$exists['id'];
    $upd->bind_param('ssi', $coverLetter, $relativePath, $appId);
    if (!$upd->execute()) throw new Exception('Failed to update existing application.');
    $upd->close();
    $applicationId = $appId;
  } else {
    // Build INSERT based on available columns
    if ($hasApplicantCol && $hasUserIdCol) {
      $ins = $conn->prepare('INSERT INTO applications (job_id, applicant_id, user_id, cover_letter, resume_path, status, applied_at) VALUES (?, ?, ?, ?, ?, "pending", NOW())');
      if (!$ins) throw new Exception('Prepare failed: ' . $conn->error);
      $ins->bind_param('iiiss', $jobId, $userId, $userId, $coverLetter, $relativePath);
    } elseif ($hasUserIdCol) {
      $ins = $conn->prepare('INSERT INTO applications (job_id, user_id, cover_letter, resume_path, status, applied_at) VALUES (?, ?, ?, ?, "pending", NOW())');
      if (!$ins) throw new Exception('Prepare failed: ' . $conn->error);
      $ins->bind_param('iiss', $jobId, $userId, $coverLetter, $relativePath);
    } else {
      $ins = $conn->prepare('INSERT INTO applications (job_id, applicant_id, cover_letter, resume_path, status, applied_at) VALUES (?, ?, ?, ?, "pending", NOW())');
      if (!$ins) throw new Exception('Prepare failed: ' . $conn->error);
      $ins->bind_param('iiss', $jobId, $userId, $coverLetter, $relativePath);
    }
    if (!$ins->execute()) throw new Exception('Could not save application.');
    $applicationId = $ins->insert_id;
    $ins->close();
  }

  // Notify employer if notifications table exists
  $hasNotif = $conn->query("SHOW TABLES LIKE 'notifications'");
  if ($hasNotif && $hasNotif->num_rows > 0) {
    $title = 'New Application Received';
    $message = 'A job seeker applied to your job: ' . ($job['title'] ?? '');
    $n = $conn->prepare('INSERT INTO notifications (user_id, title, message, type, reference_id, reference_type) VALUES (?, ?, ?, "info", ?, "application")');
    if ($n) { $n->bind_param('issi', $employerId, $title, $message, $applicationId); $n->execute(); $n->close(); }
  }

  $conn->commit();

  $resp['success'] = true;
  $resp['message'] = 'Application submitted.';
  echo json_encode($resp);
  exit;
} catch (Throwable $e) {
  if (isset($conn)) { @mysqli_rollback($conn); }
  $resp['message'] = $e->getMessage();
  echo json_encode($resp);
  exit;
}
