<x-guest-layout>
    <div class="py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6 mb-6">
                <form method="GET" class="grid gap-4 md:grid-cols-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Keyword</label>
                        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="w-full rounded-md border-gray-300" placeholder="Role, company, skills">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <input type="text" name="location" value="{{ $filters['location'] ?? '' }}" class="w-full rounded-md border-gray-300" placeholder="City or remote">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Employment Type</label>
                        <select name="employment_type" class="w-full rounded-md border-gray-300">
                            <option value="">Any</option>
                            @foreach (['full_time' => 'Full Time', 'part_time' => 'Part Time', 'contract' => 'Contract', 'internship' => 'Internship'] as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['employment_type'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            Search Jobs
                        </button>
                    </div>
                </form>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                @forelse ($jobs as $job)
                    <div class="bg-white shadow-sm rounded-lg p-6 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start gap-4">
                                <div>
                                    <p class="text-sm text-gray-500 uppercase tracking-wide">{{ $job->company_name }}</p>
                                    <h3 class="text-xl font-semibold text-gray-900 mt-1">
                                        <a href="{{ route('jobs.show', $job) }}" class="hover:text-indigo-600">{{ $job->title }}</a>
                                    </h3>
                                </div>
                                <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                                    {{ Str::headline($job->employment_type) }}
                                </span>
                            </div>
                            <div class="mt-4 text-sm text-gray-600 flex flex-wrap gap-4">
                                <span><i class="fa-solid fa-location-dot text-gray-400 me-1"></i>{{ $job->location }}</span>
                                @if ($job->salary)
                                    <span><i class="fa-solid fa-coins text-gray-400 me-1"></i>{{ number_format($job->salary, 2) }}</span>
                                @endif
                                <span><i class="fa-solid fa-calendar text-gray-400 me-1"></i>{{ optional($job->published_at)->diffForHumans() }}</span>
                            </div>
                            <p class="mt-4 text-gray-700 text-sm">
                                {{ Str::limit(strip_tags($job->description), 180) }}
                            </p>
                            @if ($job->required_skills)
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach ($job->required_skills as $skill)
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                            {{ $skill }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="mt-6 flex justify-between items-center">
                            <a href="{{ route('jobs.show', $job) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">
                                View Details →
                            </a>
                            @auth
                                <form method="POST" action="{{ route('jobs.apply', $job) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                        Apply Now
                                    </button>
                                </form>
                            @endauth
                        </div>
                    </div>
                @empty
                    <div class="col-span-2">
                        <div class="bg-white shadow rounded-lg p-8 text-center">
                            <h3 class="text-lg font-semibold mb-2">No jobs found</h3>
                            <p class="text-gray-600">Adjust your filters and try searching again.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $jobs->links() }}
            </div>
        </div>
    </div>
</x-guest-layout>

