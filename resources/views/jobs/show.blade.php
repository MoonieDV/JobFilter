<x-guest-layout>
    <div class="py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-8">
                <div class="flex flex-wrap justify-between gap-4">
                    <div>
                        <p class="text-sm text-gray-500 uppercase tracking-wide">{{ $job->company_name }}</p>
                        <h1 class="text-3xl font-bold text-gray-900 mt-1">{{ $job->title }}</h1>
                        <div class="mt-3 flex flex-wrap gap-4 text-sm text-gray-600">
                            <span><i class="fa-solid fa-location-dot text-gray-400 me-1"></i>{{ $job->location }}</span>
                            <span><i class="fa-solid fa-briefcase text-gray-400 me-1"></i>{{ Str::headline($job->employment_type) }}</span>
                            @if ($job->salary)
                                <span><i class="fa-solid fa-coins text-gray-400 me-1"></i>{{ number_format($job->salary, 2) }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Posted {{ optional($job->published_at)->diffForHumans() }}</p>
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 mt-2">
                            {{ ucfirst($job->status) }}
                        </span>
                    </div>
                </div>

                <div class="mt-8 grid gap-6 md:grid-cols-3">
                    <div class="md:col-span-2 space-y-6">
                        <section>
                            <h2 class="text-lg font-semibold text-gray-900">Description</h2>
                            <div class="mt-3 prose prose-indigo max-w-none">
                                {!! nl2br(e($job->description)) !!}
                            </div>
                        </section>

                        @if ($job->responsibilities)
                            <section>
                                <h2 class="text-lg font-semibold text-gray-900">Responsibilities</h2>
                                <div class="mt-3 prose prose-indigo max-w-none">
                                    {!! nl2br(e($job->responsibilities)) !!}
                                </div>
                            </section>
                        @endif

                        @if ($job->requirements)
                            <section>
                                <h2 class="text-lg font-semibold text-gray-900">Requirements</h2>
                                <div class="mt-3 prose prose-indigo max-w-none">
                                    {!! nl2br(e($job->requirements)) !!}
                                </div>
                            </section>
                        @endif
                    </div>

                    <div class="space-y-6">
                        <div class="border border-gray-100 rounded-lg p-4">
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-3">Key Skills</h3>
                            <div class="flex flex-wrap gap-2">
                                @forelse ($job->required_skills ?? [] as $skill)
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                        {{ $skill }}
                                    </span>
                                @empty
                                    <p class="text-sm text-gray-500">No specific skills listed.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="border border-gray-100 rounded-lg p-4">
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-3">Apply Now</h3>
                            @auth
                                <form method="POST" action="{{ route('jobs.apply', $job) }}" enctype="multipart/form-data" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Cover Letter</label>
                                        <textarea name="cover_letter" rows="4" class="mt-1 w-full rounded-md border-gray-300">{{ old('cover_letter') }}</textarea>
                                        <x-input-error :messages="$errors->get('cover_letter')" class="mt-2" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Resume (PDF/DOC, max 5MB)</label>
                                        <input type="file" name="resume" class="mt-1 w-full text-sm text-gray-500">
                                        <x-input-error :messages="$errors->get('resume')" class="mt-2" />
                                    </div>
                                    <button type="submit" class="w-full inline-flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                        Submit Application
                                    </button>
                                </form>
                            @else
                                <p class="text-sm text-gray-600">Please <a href="{{ route('login') }}" class="font-semibold text-indigo-600">sign in</a> to apply.</p>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>

