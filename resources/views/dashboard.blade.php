<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Welcome back, {{ $user->firstname ?? $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <div class="grid gap-6 md:grid-cols-3">
                @foreach ($metrics as $label => $value)
                    <div class="bg-white shadow-sm rounded-lg p-6">
                        <p class="text-sm text-gray-500 uppercase tracking-wide">{{ Str::headline(str_replace('_', ' ', $label)) }}</p>
                        <p class="mt-3 text-3xl font-semibold text-gray-900">{{ number_format($value) }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Opportunities</h3>
                        <a href="{{ route('jobs.browse') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">View all</a>
                    </div>
                    <div class="space-y-4">
                        @forelse ($recentJobs as $job)
                            <div class="border border-gray-100 rounded-md p-4">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $job->title }}</p>
                                        <p class="text-sm text-gray-500">{{ $job->company_name }} · {{ $job->location }}</p>
                                    </div>
                                    <span class="text-xs text-gray-400">{{ $job->published_at?->diffForHumans() }}</span>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach (($job->required_skills ?? []) as $skill)
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-700">{{ $skill }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No jobs published yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ $user->role === 'employer' ? 'Recent Applications' : 'My Applications' }}
                        </h3>
                        <a href="{{ route('applications.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Manage</a>
                    </div>
                    <div class="space-y-4">
                        @forelse ($recentApplications as $application)
                            <div class="border border-gray-100 rounded-md p-4">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $application->job->title }}</p>
                                        <p class="text-sm text-gray-500">
                                            @if ($user->role === 'employer')
                                                {{ $application->applicant->name ?? $application->full_name }}
                                            @else
                                                {{ $application->job->company_name }}
                                            @endif
                                        </p>
                                    </div>
                                    <span class="text-xs text-gray-400">{{ $application->applied_at?->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-gray-600 mt-2">Status: <span class="font-medium">{{ ucfirst($application->status) }}</span></p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No applications yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
