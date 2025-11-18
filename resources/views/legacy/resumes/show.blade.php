<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resume: {{ $candidate->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Resume: {{ $candidate->name }}</h2>
        <p><strong>Email:</strong> {{ $candidate->email }}</p>
        <p><strong>File:</strong> {{ basename($candidate->resume_path ?? '') }}</p>
        <p><strong>Uploaded:</strong> {{ optional($candidate->updated_at ?? $candidate->created_at)->format('Y-m-d H:i') }}</p>
        <p>
            <a href="{{ route('resumes.download', $candidate) }}" class="btn btn-primary btn-sm">Download Resume</a>
            <a href="{{ route('resumes.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
        </p>

        <h3>Resume Preview</h3>
        @if ($resumeUrl)
            <div class="border rounded" style="height: 500px;">
                <iframe src="{{ $resumeUrl }}" title="Resume Preview" style="width:100%;height:100%;border:0;" loading="lazy"></iframe>
            </div>
            <small class="text-muted d-block mt-2">Preview works best for PDF files. Download the resume for full fidelity.</small>
        @else
            <div class="alert alert-info">No preview available for this resume.</div>
        @endif
    </div>
</body>
</html>

