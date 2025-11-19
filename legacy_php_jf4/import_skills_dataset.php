<?php
/**
 * Import comprehensive skills dataset
 */

require_once __DIR__ . '/db_connect.php';

// Comprehensive skills dataset
$skillsDataset = [
    'Programming Languages' => [
        'PHP', 'JavaScript', 'Python', 'Java', 'C#', 'C++', 'C', 'Ruby', 'Go', 'Rust', 
        'Swift', 'Kotlin', 'Scala', 'R', 'MATLAB', 'Perl', 'Lua', 'Dart', 'TypeScript',
        'Objective-C', 'Assembly', 'COBOL', 'Fortran', 'Pascal', 'Delphi', 'VB.NET',
        'Clojure', 'Haskell', 'Erlang', 'Elixir', 'F#', 'OCaml', 'Lisp', 'Scheme'
    ],
    'Web Technologies' => [
        'HTML', 'CSS', 'HTML5', 'CSS3', 'Bootstrap', 'Tailwind CSS', 'Sass', 'Less', 
        'jQuery', 'React', 'Angular', 'Vue.js', 'Node.js', 'Express.js', 'Laravel', 
        'Symfony', 'CodeIgniter', 'Django', 'Flask', 'Spring', 'ASP.NET', 'Ruby on Rails',
        'Next.js', 'Nuxt.js', 'Svelte', 'Ember.js', 'Backbone.js', 'Meteor', 'Webpack',
        'Vite', 'Parcel', 'Rollup', 'Gulp', 'Grunt', 'Babel', 'ESLint', 'Prettier'
    ],
    'Databases' => [
        'MySQL', 'PostgreSQL', 'MongoDB', 'SQLite', 'Oracle', 'SQL Server', 'Redis', 
        'Elasticsearch', 'Cassandra', 'DynamoDB', 'Firebase', 'MariaDB', 'CouchDB',
        'Neo4j', 'ArangoDB', 'InfluxDB', 'TimescaleDB', 'CockroachDB', 'PlanetScale'
    ],
    'Cloud & DevOps' => [
        'AWS', 'Azure', 'Google Cloud', 'Docker', 'Kubernetes', 'Jenkins', 'GitLab CI', 
        'GitHub Actions', 'Terraform', 'Ansible', 'Chef', 'Puppet', 'Vagrant', 'Vagrant',
        'Helm', 'Istio', 'Linkerd', 'Prometheus', 'Grafana', 'ELK Stack', 'Fluentd',
        'Consul', 'Vault', 'Nomad', 'Rancher', 'OpenShift', 'CloudFoundry'
    ],
    'Cybersecurity' => [
        'McAfee', 'SIEM', 'EPO', 'NSM', 'FireEye', 'CMS', 'ETP', 'Wireshark', 'TCPView', 
        'OllyDbg', 'WinDbg', 'GBD', 'Nmap', 'Metasploit', 'Burp Suite', 'Nessus', 
        'OpenVAS', 'Snort', 'Suricata', 'Splunk', 'QRadar', 'ArcSight', 'Carbon Black', 
        'CrowdStrike', 'Palo Alto', 'Check Point', 'Fortinet', 'Cisco', 'Juniper',
        'Kali Linux', 'OWASP', 'ZAP', 'Aircrack-ng', 'John the Ripper', 'Hashcat'
    ],
    'Data Science & AI' => [
        'Machine Learning', 'Deep Learning', 'TensorFlow', 'PyTorch', 'Scikit-learn', 
        'Pandas', 'NumPy', 'Matplotlib', 'Seaborn', 'Jupyter', 'Tableau', 'Power BI', 
        'Apache Spark', 'Hadoop', 'Kafka', 'Airflow', 'dbt', 'MLflow', 'Kubeflow',
        'OpenAI', 'Hugging Face', 'LangChain', 'LlamaIndex', 'RAG', 'Vector Databases'
    ],
    'Mobile Development' => [
        'React Native', 'Flutter', 'Xamarin', 'Ionic', 'Cordova', 'PhoneGap', 
        'Android Studio', 'Xcode', 'SwiftUI', 'Jetpack Compose', 'Kotlin Multiplatform',
        'Unity', 'Unreal Engine', 'Godot', 'Cocos2d', 'SpriteKit'
    ],
    'Game Development' => [
        'Unity', 'Unreal Engine', 'Godot', 'Cocos2d', 'SpriteKit', 'Phaser', 'Three.js',
        'WebGL', 'OpenGL', 'DirectX', 'Vulkan', 'Blender', 'Maya', '3ds Max'
    ],
    'Blockchain & Web3' => [
        'Solidity', 'Web3.js', 'Ethers.js', 'Hardhat', 'Truffle', 'Ganache', 'Remix',
        'IPFS', 'Arweave', 'Ethereum', 'Bitcoin', 'Polygon', 'Avalanche', 'Solana'
    ],
    'IoT & Embedded' => [
        'Arduino', 'Raspberry Pi', 'ESP32', 'STM32', 'MicroPython', 'CircuitPython',
        'MQTT', 'CoAP', 'LoRaWAN', 'Zigbee', 'Bluetooth', 'WiFi', 'Ethernet'
    ]
];

// Create categories and skills
foreach ($skillsDataset as $categoryName => $skills) {
    // Insert category
    $categoryQuery = "INSERT IGNORE INTO skill_categories (name) VALUES (?)";
    $stmt = $conn->prepare($categoryQuery);
    $stmt->bind_param('s', $categoryName);
    $stmt->execute();
    
    // Get category ID
    $categoryIdQuery = "SELECT id FROM skill_categories WHERE name = ?";
    $stmt = $conn->prepare($categoryIdQuery);
    $stmt->bind_param('s', $categoryName);
    $stmt->execute();
    $categoryId = $stmt->get_result()->fetch_assoc()['id'];
    
    // Insert skills
    foreach ($skills as $skill) {
        $skillQuery = "INSERT IGNORE INTO skills (name, category_id) VALUES (?, ?)";
        $stmt = $conn->prepare($skillQuery);
        $stmt->bind_param('si', $skill, $categoryId);
        $stmt->execute();
    }
}

echo "Skills dataset imported successfully!";
?>
