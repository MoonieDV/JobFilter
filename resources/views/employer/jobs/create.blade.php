<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Post a Job') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-8">
                <form method="POST" action="{{ route('employer.jobs.store') }}">
                    @include('employer.jobs._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

