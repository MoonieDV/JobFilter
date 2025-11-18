<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Candidate Resumes - JobFilter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Candidate Resumes</h1>

        @if ($candidates->isEmpty())
            <div class="alert alert-info">No resumes available yet.</div>
        @else
            <div class="row">
                @foreach ($candidates as $candidate)
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">{{ $candidate->name }}</h5>
                                <p class="card-text">{{ $candidate->email }}</p>
                                <p class="text-muted small">
                                    Resume: {{ basename($candidate->resume_path) }}<br>
                                    Uploaded: {{ optional($candidate->updated_at ?? $candidate->created_at)->format('Y-m-d H:i') }}
                                </p>
                                <div class="btn-group">
                                    <a href="{{ route('resumes.show', $candidate) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    <a href="{{ route('resumes.download', $candidate) }}" class="btn btn-sm btn-primary">Download</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-4 d-flex align-items-center gap-3">
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
            <form method="POST" action="{{ route('logout') }}" class="needs-logout-confirm">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">Logout</button>
            </form>
        </div>
    </div>
</body>
</html>

