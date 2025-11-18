<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ Auth::user()->role === 'employer' ? __('Incoming Applications') : __('My Applications') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job</th>
                            @if (Auth::user()->role === 'employer')
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applicant</th>
                            @endif
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applied</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($applications as $application)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $application->job->title }}</div>
                                    <div class="text-sm text-gray-500">{{ $application->job->company_name }}</div>
                                </td>
                                @if (Auth::user()->role === 'employer')
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $application->applicant->name ?? $application->full_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $application->email }}</div>
                                    </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold
                                        @class([
                                            'bg-indigo-50 text-indigo-700' => $application->status === 'pending',
                                            'bg-green-50 text-green-700' => $application->status === 'shortlisted' || $application->status === 'hired',
                                            'bg-red-50 text-red-700' => $application->status === 'rejected',
                                        ])">
                                        {{ ucfirst($application->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $application->applied_at?->diffForHumans() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                    @if ($application->resume_path)
                                        <a href="{{ asset('storage/'.$application->resume_path) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">
                                            Resume
                                        </a>
                                    @endif
                                    @if ($application->cover_letter)
                                        <button x-data x-on:click="$dispatch('open-modal', 'cover-{{ $application->id }}')" class="text-gray-600 hover:text-gray-900">
                                            Cover Letter
                                        </button>
                                    @endif
                                    @if (Auth::user()->role !== 'employer')
                                        <form method="POST" action="{{ route('applications.destroy', $application) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Withdraw this application?')">Withdraw</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>

                            @if ($application->cover_letter)
                                <x-modal name="cover-{{ $application->id }}" focusable>
                                    <div class="p-6">
                                        <h2 class="text-lg font-medium text-gray-900 mb-4">Cover Letter</h2>
                                        <p class="text-gray-700 whitespace-pre-line">{{ $application->cover_letter }}</p>
                                        <div class="mt-6 text-right">
                                            <x-secondary-button x-on:click="$dispatch('close')">
                                                Close
                                            </x-secondary-button>
                                        </div>
                                    </div>
                                </x-modal>
                            @endif
                        @empty
                            <tr>
                                <td colspan="{{ Auth::user()->role === 'employer' ? 5 : 4 }}" class="px-6 py-6 text-center text-sm text-gray-500">
                                    No applications found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $applications->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

