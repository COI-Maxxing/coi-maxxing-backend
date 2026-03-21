<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ValidateDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth('sanctum')->user();
        return $user && in_array($user->role, ['admin', 'pm']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'insurer' => ['nullable', 'string', 'max:500'],
            'policy_number' => ['nullable', 'string', 'max:255'],
            'coverage_amount' => ['nullable', 'numeric', 'gt:0', 'max:9999999999999.99'],
            'expiry_date' => ['nullable', 'date', 'date_format:Y-m-d', 'after:today'],
            'holder_name' => ['nullable', 'string', 'max:500']
        ];
    }
}
