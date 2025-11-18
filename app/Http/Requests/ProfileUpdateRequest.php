<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'firstname' => ['nullable', 'string', 'max:100'],
            'lastname' => ['nullable', 'string', 'max:100'],
            'job_title' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'dob' => ['nullable', 'date'],
            'bio' => ['nullable', 'string'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:30'],
            'company_linkedin' => ['nullable', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];
    }
}
