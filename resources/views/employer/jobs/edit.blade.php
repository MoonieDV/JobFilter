<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Job') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-8">
                @if (session('status'))
                    <div class="mb-4 rounded-md bg-green-50 border border-green-200 p-4 text-sm text-green-800">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('employer.jobs.update', $job) }}">
                    @include('employer.jobs._form', ['job' => $job])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

