<?php
/**
 * Simple manual skill addition
 */

require_once __DIR__ . '/db_connect.php';

echo "<h1>Add Skills Manually</h1>";

// Your cybersecurity skills
$skills = [
    'McAfee', 'SIEM', 'EPO', 'NSM',
    'FireEye', 'CMS', 'ETP',
    'OllyDbg', 'WinDbg', 'GBD',
    'Wireshark', 'TCPView',
    'DNS', 'Mail Server',
    'Windows 10', 'Windows 11', 'Linux', 'Mac OS',
    'Google Workspace'
];

echo "<h2>Adding these skills:</h2>";
echo "<ul>";
foreach ($skills as $skill) {
    echo "<li>" . htmlspecialchars($skill) . "</li>";
}
echo "</ul>";

// Get the latest user
$result = $conn->query("SELECT id FROM users ORDER BY id DESC LIMIT 1");
if ($result && $result->num_rows > 0) {
    $userId = $result->fetch_assoc()['id'];
    echo "<h2>Adding skills to User ID: " . $userId . "</h2>";
    
    // Clear existing skills
    $conn->query("DELETE FROM user_skills WHERE user_id = $userId");
    echo "<p>✅ Cleared existing skills</p>";
    
    // Add new skills
    $addedCount = 0;
    foreach ($skills as $skill) {
        $stmt = $conn->prepare("INSERT INTO user_skills (user_id, skill_name, confidence_score, extracted_from) VALUES (?, ?, 1.00, 'manual')");
        $stmt->bind_param("is", $userId, $skill);
        if ($stmt->execute()) {
            $addedCount++;
        }
        $stmt->close();
    }
    
    echo "<p style='color: green;'>✅ Added " . $addedCount . " skills</p>";
    
    // Show skills in database
    $result = $conn->query("SELECT skill_name FROM user_skills WHERE user_id = $userId ORDER BY skill_name");
    echo "<h3>Skills now in database:</h3>";
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>" . htmlspecialchars($row['skill_name']) . "</li>";
    }
    echo "</ul>";
    
} else {
    echo "<h2>No users found</h2>";
    echo "<p>Please register a user first.</p>";
}

echo "<hr>";
echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>✅ Skills have been added to the database</li>";
echo "<li>🔄 Go to your dashboard (dashboard.php)</li>";
echo "<li>📊 Check the 'Your Skills Profile' section</li>";
echo "<li>🎯 You should see all your cybersecurity skills displayed</li>";
echo "</ol>";

echo "<h2>Alternative: Use Dashboard Manual Input</h2>";
echo "<p>You can also use the dashboard's 'Update Skills' feature to manually add skills through the web interface.</p>";
?>
