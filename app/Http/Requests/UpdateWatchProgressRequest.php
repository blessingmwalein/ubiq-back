<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWatchProgressRequest extends FormRequest
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
            'profile_id' => ['required', 'exists:profiles,id'],
            'content_item_id' => ['required', 'exists:content_items,id'],
            'progress_seconds' => ['required', 'integer', 'min:0'],
            'duration_seconds' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'profile_id.required' => 'Profile is required',
            'profile_id.exists' => 'Profile not found',
            'content_item_id.required' => 'Content item is required',
            'content_item_id.exists' => 'Content not found',
            'progress_seconds.required' => 'Progress time is required',
            'progress_seconds.integer' => 'Progress must be a number',
            'progress_seconds.min' => 'Progress cannot be negative',
            'duration_seconds.required' => 'Duration is required',
            'duration_seconds.min' => 'Duration must be at least 1 second',
        ];
    }
}
