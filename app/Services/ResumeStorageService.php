<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResumeStorageService
{
    public function store(UploadedFile $file, ?int $userId = null): string
    {
        $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $extension = $file->getClientOriginalExtension();
        $fileName = sprintf(
            '%s_%s_%s.%s',
            now()->format('YmdHis'),
            $userId ?? 'guest',
            $safeName ?: 'resume',
            $extension
        );

        return $file->storeAs('resumes', $fileName, 'public');
    }
}

