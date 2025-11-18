@foreach ([
    'Christian' => [
        'title' => 'Christian Crisostomo - Resume',
        'summary' => 'Project Manager with experience in agile delivery, stakeholder management, and cross-functional leadership.',
        'details' => [
            'Skills: Agile, Scrum, Roadmapping, Risk Management, Jira',
            'Experience: 5+ years leading software projects',
            'Education: B.S. in Information Technology',
        ],
    ],
    'Alyssa' => [
        'title' => 'Alyssa Jane Prak - Resume',
        'summary' => 'System Analyst and Database Designer specializing in requirements analysis and relational data modeling.',
        'details' => [
            'Skills: SQL, ERD, Normalization, UML, Documentation',
            'Experience: 3+ years in system analysis and DB design',
            'Education: B.S. in Computer Science',
        ],
    ],
    'Frank' => [
        'title' => 'Frank Oliver Bentoy - Resume',
        'summary' => 'Software Engineer focusing on frontend and backend web development with modern frameworks.',
        'details' => [
            'Skills: JavaScript, C#, Java, C, C++, Node.js',
            'Experience: Built responsive web apps and dashboards',
            'Education: B.S. in Information Systems',
        ],
    ],
    'Shine' => [
        'title' => 'Shine Florence Padillo - Resume',
        'summary' => 'Software Tester and Technical Writer with experience in QA processes and documentation.',
        'details' => [
            'Skills: Test Planning, Manual Testing, Bug Reporting, Technical Writing',
            'Experience: Contributed to multiple product releases',
            'Education: B.S. in Information Technology',
        ],
    ],
] as $key => $info)
    <div class="modal fade" id="resume{{ $key }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $info['title'] }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">{{ $info['summary'] }}</p>
                    <ul>
                        @foreach ($info['details'] as $detail)
                            <li><strong>{{ Str::before($detail, ':') }}:</strong> {{ Str::after($detail, ': ') }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endforeach

