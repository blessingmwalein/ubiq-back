<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeRequest extends FormRequest
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
            'account_id' => ['required', 'exists:accounts,id'],
            'package_id' => ['required', 'exists:packages,id'],
            'payment_method' => ['required', 'in:stripe,paypal,credit_card'],
            'payment_token' => ['nullable', 'string'],
            'is_trial' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'account_id.required' => 'Account is required',
            'account_id.exists' => 'Account not found',
            'package_id.required' => 'Please select a subscription package',
            'package_id.exists' => 'Selected package does not exist',
            'payment_method.required' => 'Payment method is required',
            'payment_method.in' => 'Invalid payment method selected',
        ];
    }
}
