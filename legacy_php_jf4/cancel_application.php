<?php
// cancel_application.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';

try {
    if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Invalid method']);
        exit;
    }
    $userId = (int)$_SESSION['user_id'];
    $appId = isset($_POST['application_id']) ? (int)$_POST['application_id'] : 0;
    if ($appId <= 0) {
        throw new Exception('Invalid application');
    }

    // Check ownership, current status, and fetch job_id
    $stmt = $conn->prepare('SELECT a.id, a.status, a.job_id FROM applications a WHERE a.id = ? AND a.applicant_id = ? LIMIT 1');
    $stmt->bind_param('ii', $appId, $userId);
    $stmt->execute();
    $app = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$app) {
        throw new Exception('Application not found');
    }

    $statusRaw = isset($app['status']) ? (string)$app['status'] : '';
    $statusTrim = trim($statusRaw);
    $statusNorm = strtolower(preg_replace('/\s+/', ' ', $statusTrim));
    if ($statusNorm === '' || $statusNorm === null) { $statusNorm = 'pending'; }
    // Only block if explicitly finalized
    $blocked = in_array($statusNorm, ['hired','rejected','cancelled'], true);
    if ($blocked) {
        throw new Exception('This application can no longer be cancelled');
    }

    $jobId = (int)$app['job_id'];

    // Delete the application record (hard delete)
    $del = $conn->prepare('DELETE FROM applications WHERE id = ? AND applicant_id = ?');
    $del->bind_param('ii', $appId, $userId);
    if (!$del->execute()) { throw new Exception('Failed to delete application'); }
    $deleted = $del->affected_rows;
    $del->close();

    // Also delete any other non-final rows for same job (defensive cleanup)
    $deletedExtra = 0;
    if ($jobId > 0) {
        $delAll = $conn->prepare("DELETE FROM applications WHERE applicant_id = ? AND job_id = ?");
        if ($delAll) { $delAll->bind_param('ii', $userId, $jobId); $delAll->execute(); $deletedExtra = $delAll->affected_rows; $delAll->close(); }
    }

    echo json_encode(['success' => true, 'message' => 'Application removed', 'deleted' => ($deleted + $deletedExtra), 'job_id' => $jobId]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
