<?php
/**
 * Enhanced Resume Storage with Database Content Storage
 */

class ResumeManager {
    private $conn;
    private $uploadDir;
    
    public function __construct($databaseConnection, $uploadDirectory) {
        $this->conn = $databaseConnection;
        $this->uploadDir = $uploadDirectory;
        $this->ensureUploadDirectory();
    }
    
    private function ensureUploadDirectory() {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0775, true);
        }
    }
    
    /**
     * Store resume with both file and database content
     */
    public function storeResume($userId, $file, $extractText = true) {
        // Validate file
        $allowedTypes = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $allowedExts = ['pdf', 'docx'];
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts) || !in_array($file['type'], $allowedTypes)) {
            throw new Exception('Invalid file type');
        }
        
        // Generate unique filename
        $uniqueName = time() . '_' . bin2hex(random_bytes(4)) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
        $filePath = $this->uploadDir . '/' . $uniqueName;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('Failed to save file');
        }
        
        // Extract text content if requested
        $textContent = '';
        if ($extractText) {
            $textContent = $this->extractTextFromFile($filePath, $ext);
        }
        
        // Store in database
        $this->storeResumeInDatabase($userId, $filePath, $file['name'], $textContent, $file['size']);
        
        return [
            'file_path' => $filePath,
            'text_content' => $textContent,
            'file_size' => $file['size']
        ];
    }
    
    /**
     * Extract text from resume file
     */
    private function extractTextFromFile($filePath, $extension) {
        require_once __DIR__ . '/skill_extractor.php';
        $extractor = new SkillExtractor();
        
        switch ($extension) {
            case 'pdf':
                return $extractor->extractTextFromPDF($filePath);
            case 'docx':
                return $extractor->extractTextFromDOCX($filePath);
            default:
                return '';
        }
    }
    
    /**
     * Store resume metadata and content in database
     */
    private function storeResumeInDatabase($userId, $filePath, $originalName, $textContent, $fileSize) {
        // Update user's resume_path
        $updateUserQuery = "UPDATE users SET resume_path = ? WHERE id = ?";
        $stmt = $this->conn->prepare($updateUserQuery);
        $stmt->bind_param('si', $filePath, $userId);
        $stmt->execute();
        
        // Store full resume record
        $insertResumeQuery = "INSERT INTO user_resumes (user_id, file_path, original_name, file_size, text_content, created_at) 
                             VALUES (?, ?, ?, ?, ?, NOW()) 
                             ON DUPLICATE KEY UPDATE 
                             file_path = VALUES(file_path), 
                             original_name = VALUES(original_name), 
                             file_size = VALUES(file_size), 
                             text_content = VALUES(text_content), 
                             updated_at = NOW()";
        
        $stmt = $this->conn->prepare($insertResumeQuery);
        $stmt->bind_param('issis', $userId, $filePath, $originalName, $fileSize, $textContent);
        $stmt->execute();
    }
    
    /**
     * Get resume for download/viewing
     */
    public function getResume($userId) {
        $query = "SELECT file_path, original_name, text_content FROM user_resumes WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    /**
     * Serve resume file for download
     */
    public function serveResume($userId) {
        $resume = $this->getResume($userId);
        if (!$resume || !file_exists($resume['file_path'])) {
            throw new Exception('Resume not found');
        }
        
        $filePath = $resume['file_path'];
        $originalName = $resume['original_name'];
        
        // Set headers for download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $originalName . '"');
        header('Content-Length: ' . filesize($filePath));
        
        // Output file
        readfile($filePath);
        exit;
    }
    
    /**
     * Get resume text content for analysis
     */
    public function getResumeText($userId) {
        $resume = $this->getResume($userId);
        return $resume['text_content'] ?? '';
    }
}

// Database schema for enhanced resume storage
$createResumeTableSQL = "
CREATE TABLE IF NOT EXISTS user_resumes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_size INT UNSIGNED NOT NULL,
    text_content LONGTEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_resume (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
?>
