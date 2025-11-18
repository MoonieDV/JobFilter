@csrf
@if(isset($job))
    @method('PUT')
@endif

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-gray-700">Job Title</label>
        <input type="text" name="title" value="{{ old('title', $job->title ?? '') }}" class="mt-1 w-full rounded-md border-gray-300" required>
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Company Name</label>
        <input type="text" name="company_name" value="{{ old('company_name', $job->company_name ?? Auth::user()->company_name) }}" class="mt-1 w-full rounded-md border-gray-300" required>
        <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Location</label>
        <input type="text" name="location" value="{{ old('location', $job->location ?? '') }}" class="mt-1 w-full rounded-md border-gray-300" required>
        <x-input-error :messages="$errors->get('location')" class="mt-2" />
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Employment Type</label>
        <select name="employment_type" class="mt-1 w-full rounded-md border-gray-300" required>
            @foreach (['full_time' => 'Full Time', 'part_time' => 'Part Time', 'contract' => 'Contract', 'internship' => 'Internship'] as $value => $label)
                <option value="{{ $value }}" @selected(old('employment_type', $job->employment_type ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('employment_type')" class="mt-2" />
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Experience Level</label>
        <input type="text" name="experience_level" value="{{ old('experience_level', $job->experience_level ?? '') }}" class="mt-1 w-full rounded-md border-gray-300">
        <x-input-error :messages="$errors->get('experience_level')" class="mt-2" />
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Salary (optional)</label>
        <input type="number" step="0.01" name="salary" value="{{ old('salary', $job->salary ?? '') }}" class="mt-1 w-full rounded-md border-gray-300">
        <x-input-error :messages="$errors->get('salary')" class="mt-2" />
    </div>
</div>

<div class="mt-6">
    <label class="block text-sm font-medium text-gray-700">Description</label>
    <textarea name="description" rows="6" class="mt-1 w-full rounded-md border-gray-300" required>{{ old('description', $job->description ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>

<div class="mt-6 grid gap-6 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-gray-700">Responsibilities</label>
        <textarea name="responsibilities" rows="4" class="mt-1 w-full rounded-md border-gray-300">{{ old('responsibilities', $job->responsibilities ?? '') }}</textarea>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Requirements</label>
        <textarea name="requirements" rows="4" class="mt-1 w-full rounded-md border-gray-300">{{ old('requirements', $job->requirements ?? '') }}</textarea>
    </div>
</div>

<div class="mt-6 grid gap-6 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-gray-700">Required Skills (comma separated)</label>
        <input type="text" name="required_skills" value="{{ old('required_skills', isset($job) && $job->required_skills ? implode(', ', $job->required_skills) : '') }}" class="mt-1 w-full rounded-md border-gray-300">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Preferred Skills (comma separated)</label>
        <input type="text" name="preferred_skills" value="{{ old('preferred_skills', isset($job) && $job->preferred_skills ? implode(', ', $job->preferred_skills) : '') }}" class="mt-1 w-full rounded-md border-gray-300">
    </div>
</div>

<div class="mt-6">
    <label class="block text-sm font-medium text-gray-700">Status</label>
    <select name="status" class="mt-1 w-full rounded-md border-gray-300">
        @foreach (['open' => 'Open', 'draft' => 'Draft', 'closed' => 'Closed'] as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $job->status ?? 'open') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('status')" class="mt-2" />
</div>

<div class="mt-8 flex justify-end space-x-3">
    <a href="{{ route('employer.jobs.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
        Cancel
    </a>
    <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
        {{ isset($job) ? 'Update Job' : 'Publish Job' }}
    </button>
</div>

