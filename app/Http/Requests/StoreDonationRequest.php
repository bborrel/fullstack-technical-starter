<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDonationRequest extends FormRequest
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
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1'],
            'currency' => ['required', 'string', 'size:3'],
            // 'status' => ['sometimes', 'string', 'in:cancelled,completed,failed,pending,refunded'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Donation amount is required.',
            'amount.numeric' => 'Donation amount must be a number.',
            'amount.min' => 'Donation amount must be at least 1.',
            'currency.required' => 'Currency is required.',
            'currency.size' => 'Currency must be a 3-letter code.',
            // 'status.in' => 'Status must be one of: cancelled, completed, failed, pending, refunded.',
        ];
    }
}
