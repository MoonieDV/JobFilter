<?php
// process_apply.php: create application record for a job
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/check_auth.php';

try {
    // Don't redirect; return JSON if not authenticated
    if (!isAuthenticated()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }
    $userId = $_SESSION['user_id'] ?? null;
    $role = $_SESSION['role'] ?? 'job_seeker';
    if (!$userId) {
        throw new Exception('Not authenticated');
    }
    // If user_id is not numeric (legacy sessions), resolve via email
    if (!is_numeric($userId)) {
        $email = $_SESSION['email'] ?? null;
        if ($email) {
            $lookup = $conn->prepare('SELECT id FROM users WHERE email = ? AND role IN ("job_seeker","admin") LIMIT 1');
            $lookup->bind_param('s', $email);
            $lookup->execute();
            $res = $lookup->get_result()->fetch_assoc();
            $lookup->close();
            if ($res) {
                $userId = (int)$res['id'];
                $_SESSION['user_id'] = $userId; // normalize session
            }
        }
        if (!is_numeric($userId)) {
            throw new Exception('User session invalid. Please log in again.');
        }
    }
    if (!in_array($role, ['job_seeker','employee','admin'])) {
        throw new Exception('Only job seekers can apply');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request');
    }

    $jobId = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
    if ($jobId <= 0) {
        throw new Exception('Invalid job');
    }

    // Verify job exists
    $stmt = $conn->prepare('SELECT id, title, company_name, created_at FROM jobs WHERE id = ?');
    $stmt->bind_param('i', $jobId);
    $stmt->execute();
    $job = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$job) {
        throw new Exception('Job not found');
    }

    // Prevent duplicate applications by same user to same job
    $check = $conn->prepare('SELECT id FROM applications WHERE job_id = ? AND applicant_id = ? LIMIT 1');
    $check->bind_param('ii', $jobId, $userId);
    $check->execute();
    $check->store_result();
    $already = $check->num_rows > 0;
    $check->close();

    if (!$already) {
        // Check if legacy column user_id exists
        $hasUserIdCol = false;
        if ($col = $conn->query("SHOW COLUMNS FROM applications LIKE 'user_id'")) {
            $hasUserIdCol = ($col->num_rows > 0);
            $col->close();
        }
        $status = 'Under Review';
        if ($hasUserIdCol) {
            $ins = $conn->prepare('INSERT INTO applications (job_id, applicant_id, user_id, status) VALUES (?, ?, ?, ?)');
            if (!$ins) throw new Exception('Prepare failed: '.$conn->error);
            $ins->bind_param('iiis', $jobId, $userId, $userId, $status);
        } else {
            $ins = $conn->prepare('INSERT INTO applications (job_id, applicant_id, status) VALUES (?, ?, ?)');
            if (!$ins) throw new Exception('Prepare failed: '.$conn->error);
            $ins->bind_param('iis', $jobId, $userId, $status);
        }
        if (!$ins->execute()) {
            throw new Exception('Failed to apply: '.$ins->error);
        }
        $applicationId = $conn->insert_id;
        $ins->close();
    } else {
        // Get existing record id
        $get = $conn->prepare('SELECT id FROM applications WHERE job_id = ? AND applicant_id = ? LIMIT 1');
        $get->bind_param('ii', $jobId, $userId);
        $get->execute();
        $res = $get->get_result()->fetch_assoc();
        $applicationId = $res ? (int)$res['id'] : 0;
        $get->close();
    }

    echo json_encode([
        'success' => true,
        'application_id' => $applicationId,
        'job' => [
            'id' => (int)$job['id'],
            'title' => $job['title'],
            'company' => $job['company_name']
        ],
        'already_applied' => $already
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
