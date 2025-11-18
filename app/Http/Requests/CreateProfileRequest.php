<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'avatar_url' => ['nullable', 'url', 'max:500'],
            'maturity_rating' => ['required', 'in:G,PG,PG-13,R,NC-17'],
            'is_primary' => ['sometimes', 'boolean'],
            'interest_ids' => ['nullable', 'array'],
            'interest_ids.*' => ['exists:interests,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Profile name is required',
            'name.max' => 'Profile name cannot exceed 255 characters',
            'maturity_rating.required' => 'Please select a maturity rating',
            'maturity_rating.in' => 'Invalid maturity rating selected',
            'interest_ids.*.exists' => 'One or more selected interests are invalid',
        ];
    }
}
