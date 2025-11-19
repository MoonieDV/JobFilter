<?php
/**
 * Analyze the full content of the DOCX resume to see what skills are being extracted
 */

require_once __DIR__ . '/skill_extractor.php';

echo "<h1>Resume Content Analysis</h1>";

$skillExtractor = new SkillExtractor();

// Get the DOCX file
$uploadsPath = __DIR__ . '/uploads/resumes/';
$docxFiles = glob($uploadsPath . '*.docx');

if (empty($docxFiles)) {
    echo "<p>No DOCX files found</p>";
    exit();
}

$docxFile = $docxFiles[0];
echo "<h2>Analyzing: " . basename($docxFile) . "</h2>";

// Extract full text
$fullText = $skillExtractor->extractTextFromDOCX($docxFile);

echo "<h3>Full Extracted Text:</h3>";
echo "<pre style='background: #f5f5f5; padding: 15px; border: 1px solid #ddd; max-height: 400px; overflow-y: auto; white-space: pre-wrap;'>";
echo htmlspecialchars($fullText);
echo "</pre>";

echo "<h3>Text Length: " . strlen($fullText) . " characters</h3>";

// Extract skills
$extractedSkills = $skillExtractor->extractSkills($fullText);

echo "<h3>All Extracted Skills (" . count($extractedSkills) . " total):</h3>";
echo "<ul>";
foreach ($extractedSkills as $skill) {
    echo "<li>" . htmlspecialchars($skill) . "</li>";
}
echo "</ul>";

// Check for specific skill categories
echo "<h3>Skill Categories Analysis:</h3>";

$categories = [
    'Cybersecurity' => ['McAfee', 'SIEM', 'EPO', 'NSM', 'FireEye', 'CMS', 'ETP', 'Wireshark', 'TCPView', 'OllyDbg', 'WinDbg', 'GBD'],
    'Operating Systems' => ['Windows 10', 'Windows 11', 'Linux', 'Mac OS'],
    'Programming Languages' => ['C', 'C#', 'C++', 'Java', 'Python', 'Kotlin', 'R', 'Rust', 'JavaScript', 'PHP'],
    'Network/Infrastructure' => ['DNS', 'Mail Server', 'Google Workspace'],
    'Development Tools' => ['Git', 'GitHub', 'Docker', 'Kubernetes']
];

foreach ($categories as $category => $skills) {
    echo "<h4>$category Skills Found:</h4>";
    $foundSkills = [];
    foreach ($skills as $skill) {
        if (in_array($skill, $extractedSkills)) {
            $foundSkills[] = $skill;
        }
    }
    
    if (!empty($foundSkills)) {
        echo "<ul>";
        foreach ($foundSkills as $skill) {
            echo "<li style='color: green;'>✅ " . htmlspecialchars($skill) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ No $category skills found</p>";
    }
}

// Check if the text contains the expected cybersecurity skills
echo "<h3>Expected Cybersecurity Skills Check:</h3>";
$expectedSkills = [
    'McAfee', 'SIEM', 'EPO', 'NSM',
    'FireEye', 'CMS', 'ETP',
    'OllyDbg', 'WinDbg', 'GBD',
    'Wireshark', 'TCPView',
    'DNS', 'Mail Server',
    'Windows 10', 'Windows 11', 'Linux', 'Mac OS',
    'Google Workspace'
];

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Expected Skill</th><th>Found in Text</th><th>Extracted as Skill</th><th>Status</th></tr>";

foreach ($expectedSkills as $skill) {
    $foundInText = stripos($fullText, $skill) !== false;
    $extractedAsSkill = in_array($skill, $extractedSkills);
    $status = $extractedAsSkill ? '✅ Extracted' : ($foundInText ? '⚠️ Found but not extracted' : '❌ Not found');
    $color = $extractedAsSkill ? 'green' : ($foundInText ? 'orange' : 'red');
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($skill) . "</td>";
    echo "<td>" . ($foundInText ? 'Yes' : 'No') . "</td>";
    echo "<td>" . ($extractedAsSkill ? 'Yes' : 'No') . "</td>";
    echo "<td style='color: $color;'>$status</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>Why You're Seeing \"Other Skills\":</h3>";
echo "<p>The system is extracting <strong>all</strong> technical skills mentioned in your resume, including:</p>";
echo "<ul>";
echo "<li><strong>Programming languages</strong> that you may have listed in your resume</li>";
echo "<li><strong>Development tools</strong> like Git, GitHub</li>";
echo "<li><strong>General IT skills</strong> that are in the skills database</li>";
echo "</ul>";

echo "<p><strong>This is actually correct behavior!</strong> The system is designed to extract all technical skills from your resume, not just cybersecurity ones.</p>";

echo "<h3>If You Want Only Cybersecurity Skills:</h3>";
echo "<ol>";
echo "<li><strong>Edit your resume</strong> to only include cybersecurity skills</li>";
echo "<li><strong>Use the dashboard</strong> to manually remove unwanted skills</li>";
echo "<li><strong>Modify the skills database</strong> to be more selective</li>";
echo "</ol>";
?>
