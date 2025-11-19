<?php
/**
 * Enhanced Skill Extraction with Database-Driven Categories
 */

class EnhancedSkillExtractor {
    private $conn;
    private $skillsCache = [];
    private $categoriesCache = [];
    
    public function __construct($databaseConnection) {
        $this->conn = $databaseConnection;
        $this->loadSkillsFromDatabase();
    }
    
    /**
     * Load skills and categories from database
     */
    private function loadSkillsFromDatabase() {
        // Load categories
        $categoriesQuery = "SELECT id, name, parent_id FROM skill_categories ORDER BY name";
        $result = $this->conn->query($categoriesQuery);
        while ($row = $result->fetch_assoc()) {
            $this->categoriesCache[$row['id']] = $row;
        }
        
        // Load skills with aliases
        $skillsQuery = "SELECT s.id, s.name, s.category_id, s.aliases, c.name as category_name 
                       FROM skills s 
                       JOIN skill_categories c ON s.category_id = c.id 
                       ORDER BY s.popularity_score DESC";
        $result = $this->conn->query($skillsQuery);
        while ($row = $result->fetch_assoc()) {
            $this->skillsCache[$row['name']] = $row;
            
            // Add aliases to cache
            if ($row['aliases']) {
                $aliases = json_decode($row['aliases'], true);
                foreach ($aliases as $alias) {
                    $this->skillsCache[$alias] = $row;
                }
            }
        }
    }
    
    /**
     * Extract skills with category information
     */
    public function extractSkillsWithCategories($text) {
        $extractedSkills = [];
        $text = strtolower($text);
        
        foreach ($this->skillsCache as $skillName => $skillData) {
            if (strpos($text, strtolower($skillName)) !== false) {
                $extractedSkills[] = [
                    'name' => $skillData['name'],
                    'category' => $skillData['category_name'],
                    'category_id' => $skillData['category_id']
                ];
            }
        }
        
        return $extractedSkills;
    }
    
    /**
     * Get skills by category
     */
    public function getSkillsByCategory($categoryId = null) {
        if ($categoryId) {
            $query = "SELECT name, aliases FROM skills WHERE category_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $categoryId);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        
        return $this->skillsCache;
    }
    
    /**
     * Add new skill dynamically
     */
    public function addSkill($name, $categoryId, $aliases = []) {
        $query = "INSERT INTO skills (name, category_id, aliases) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $aliasesJson = json_encode($aliases);
        $stmt->bind_param('sis', $name, $categoryId, $aliasesJson);
        
        if ($stmt->execute()) {
            // Refresh cache
            $this->loadSkillsFromDatabase();
            return true;
        }
        return false;
    }
}
?>
