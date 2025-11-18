<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Job Posts') }}
            </h2>
            <a href="{{ route('employer.jobs.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                <i class="fa-solid fa-plus me-2"></i>Create Job
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 border border-green-200 p-4 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applications</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posted</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($jobs as $job)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $job->title }}</div>
                                    <div class="text-sm text-gray-500">{{ $job->location }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold
                                        @class([
                                            'bg-green-50 text-green-700' => $job->status === 'open',
                                            'bg-yellow-50 text-yellow-700' => $job->status === 'draft',
                                            'bg-gray-100 text-gray-800' => $job->status === 'closed',
                                        ])">
                                        {{ ucfirst($job->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $job->applications()->count() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ optional($job->published_at)->diffForHumans() ?? 'Not published' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <a href="{{ route('jobs.show', $job) }}" class="text-indigo-600 hover:text-indigo-900">Preview</a>
                                    <a href="{{ route('employer.jobs.edit', $job) }}" class="text-gray-600 hover:text-gray-900">Edit</a>
                                    <form action="{{ route('employer.jobs.destroy', $job) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Remove this job post?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-6 text-center text-sm text-gray-500">
                                    You have not posted any jobs yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $jobs->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

