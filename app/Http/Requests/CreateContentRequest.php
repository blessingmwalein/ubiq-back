<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateContentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'type' => ['required', 'in:movie,show,skit,afrimation,real_estate'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'genre' => ['nullable', 'string', 'max:500'],
            'provider_id' => ['required', 'exists:content_providers,id'],
            'show_id' => ['nullable', 'exists:shows,id'],
            'poster_url' => ['nullable', 'string', 'max:500'],
            'backdrop_url' => ['nullable', 'string', 'max:500'],
            'thumbnail_url' => ['nullable', 'string', 'max:500'],
            'trailer_url' => ['nullable', 'string', 'max:500'],
            'visibility' => ['sometimes', 'in:public,private,draft'],
            'maturity_rating' => ['sometimes', 'in:all,pg,pg13,r,adult'],
            'release_year' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 5)],
            'duration_seconds' => ['nullable', 'integer', 'min:1'],
            'published_at' => ['nullable', 'date'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Content title is required',
            'description.required' => 'Content description is required',
            'type.required' => 'Content type is required',
            'type.in' => 'Content type must be movie, show, skit, afrimation, or real_estate',
            'category_id.exists' => 'Selected category does not exist',
            'provider_id.required' => 'Please select a content provider',
            'provider_id.exists' => 'Selected content provider does not exist',
            'visibility.in' => 'Visibility must be public, private, or draft',
            'maturity_rating.in' => 'Maturity rating must be all, pg, pg13, r, or adult',
        ];
    }
}
