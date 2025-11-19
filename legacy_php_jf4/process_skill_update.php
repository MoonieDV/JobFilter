<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['isLoggedIn'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/skill_extractor.php';

header('Content-Type: application/json');

try {
    $currentUserId = $_SESSION['user_id'] ?? null;
    if (!$currentUserId) {
        throw new Exception('User not found');
    }
    
    // $currentUserId already contains the user ID from session
    $userId = $currentUserId;
    
    $extractedSkills = [];
    $manualSkills = [];
    
    // Process resume upload if provided
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $allowedMime = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword'];
        $allowedExt = ['pdf', 'docx', 'doc'];
        $tmpPath = $_FILES['resume']['tmp_name'];
        $originalName = $_FILES['resume']['name'];
        $fileSize = (int)$_FILES['resume']['size'];

        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmpPath) ?: 'application/octet-stream';

        if (!in_array($ext, $allowedExt, true) || !in_array($mime, $allowedMime, true)) {
            throw new Exception('Invalid file format. Please upload PDF, DOCX, or DOC files only.');
        }
        
        if ($fileSize > 5 * 1024 * 1024) { // 5MB limit
            throw new Exception('File is too large (max 5MB).');
        }

        // Extract skills from resume
        $skillExtractor = new SkillExtractor($conn);
        $extractedSkills = $skillExtractor->processResume($tmpPath, $ext);
    }
    
    // Process manual skills if provided
    if (!empty($_POST['manualSkills'])) {
        $manualSkillsInput = trim($_POST['manualSkills']);
        if (!empty($manualSkillsInput)) {
            $manualSkills = array_map('trim', explode(',', $manualSkillsInput));
            $manualSkills = array_filter($manualSkills); // Remove empty values
        }
    }
    
    // Combine all skills
    $allSkills = array_unique(array_merge($extractedSkills, $manualSkills));
    
    if (empty($allSkills)) {
        throw new Exception('No skills provided. Please upload a resume or enter skills manually.');
    }
    
    // Save skills to database
    $skillExtractor = new SkillExtractor($conn);
    $success = $skillExtractor->saveSkillsToDatabase($userId, $allSkills, $conn);
    
    if (!$success) {
        throw new Exception('Failed to save skills to database.');
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Skills updated successfully!',
        'skills_count' => count($allSkills),
        'skills' => $allSkills
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>
