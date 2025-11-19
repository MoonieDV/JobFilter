<?php

namespace Database\Seeders;

use App\Models\Skill;
use App\Models\SkillCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SkillCategorySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the skill categories and skills into the database.
     */
    public function run(): void
    {
        // Create main categories
        $categories = [
            'Backend Development' => [
                'PHP', 'Laravel', 'Symfony', 'Node.js', 'Express.js', 'Python', 'Django', 
                'Java', 'Spring', 'C#', '.NET', 'Ruby on Rails', 'Go', 'Rust'
            ],
            'Frontend Development' => [
                'JavaScript', 'TypeScript', 'React', 'Vue.js', 'Angular', 'Svelte', 
                'HTML', 'CSS', 'Bootstrap', 'Tailwind', 'Next.js', 'Nuxt.js'
            ],
            'Mobile Development' => [
                'React Native', 'Flutter', 'Swift', 'Kotlin', 'Objective-C', 
                'Xamarin', 'Ionic', 'Android', 'iOS'
            ],
            'Database' => [
                'MySQL', 'PostgreSQL', 'MongoDB', 'Redis', 'SQLite', 'Oracle', 
                'SQL Server', 'DynamoDB', 'Firebase', 'Cassandra', 'Elasticsearch'
            ],
            'DevOps & Cloud' => [
                'Docker', 'Kubernetes', 'AWS', 'Azure', 'Google Cloud', 'Terraform',
                'Jenkins', 'GitLab CI', 'GitHub Actions', 'CloudFlare', 'Heroku'
            ],
            'Version Control' => [
                'Git', 'GitHub', 'GitLab', 'Bitbucket', 'SVN', 'Mercurial'
            ],
            'Development Tools' => [
                'Git', 'DNS', 'Mail Server', 'Linux', 'Windows Server', 'Apache', 
                'Nginx', 'IIS', 'Gradle', 'Maven', 'npm', 'Composer', 'Webpack'
            ],
            'Cybersecurity' => [
                'McAfee', 'OEM', 'EPO', 'HSM', 'FireEye', 'CMS', 'ETP', 
                'Wireshark', 'ICQView', 'SIEM', 'Intrusion Detection', 'Penetration Testing',
                'SSL/TLS', 'Encryption', 'Authentication', 'Authorization'
            ],
            'Network & Infrastructure' => [
                'DNS', 'TCP/IP', 'VPN', 'Firewall', 'Load Balancing', 'Network Architecture',
                'AWS', 'Azure', 'GCP', 'VMware', 'Hyper-V'
            ],
            'Testing' => [
                'PHPUnit', 'Pytest', 'Jest', 'Mocha', 'Jasmine', 'TestNG', 
                'Selenium', 'Cypress', 'JUnit', 'Postman', 'LoadRunner'
            ],
            'API Development' => [
                'REST API', 'GraphQL', 'gRPC', 'SOAP', 'OAuth', 'JWT', 
                'API Gateway', 'Swagger', 'OpenAPI', 'Postman'
            ],
            'Project Management' => [
                'Jira', 'Confluence', 'Trello', 'Asana', 'Monday.com', 'Scrum', 'Agile', 'Kanban'
            ],
            'Data Science & Analytics' => [
                'Python', 'R', 'Pandas', 'NumPy', 'Scikit-learn', 'TensorFlow', 
                'PyTorch', 'Jupyter', 'Tableau', 'Power BI', 'SQL', 'Machine Learning'
            ],
            'Message Queues' => [
                'RabbitMQ', 'Apache Kafka', 'Redis', 'AWS SQS', 'AWS SNS', 'RabbitMQ'
            ],
            'Search Engines' => [
                'Elasticsearch', 'Solr', 'Sphinx', 'Lucene'
            ],
        ];

        foreach ($categories as $categoryName => $skills) {
            // Create or get category
            $category = SkillCategory::firstOrCreate(
                ['name' => $categoryName],
                ['description' => $categoryName . ' skills']
            );

            // Create skills for this category
            foreach ($skills as $skillName) {
                Skill::firstOrCreate(
                    ['name' => $skillName],
                    [
                        'category_id' => $category->id,
                        'popularity_score' => random_int(50, 100) / 10,
                    ]
                );
            }

            $this->command->info("Created category: {$categoryName} with " . count($skills) . " skills");
        }

        $this->command->info('Skill categories and skills seeded successfully!');
    }
}
