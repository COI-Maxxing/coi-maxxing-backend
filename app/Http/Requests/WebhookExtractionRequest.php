<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WebhookExtractionRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'document_id' => ['required', 'uuid', 'exists:documents,id'],
            'status' => ['required', 'string', 'in:success,failed'],
            'insurer' => ['nullable', 'string', 'max:500', Rule::requiredIf($this->status === 'success')],
            'policy_number' => ['nullable', 'string', 'max:255', Rule::requiredIf($this->status === 'success')],
            'coverage_amount' => ['nullable', 'numeric', 'gt:0', Rule::requiredIf($this->status === 'success')],
            'expiry_date' => ['nullable', 'date_format:Y-m-d', Rule::requiredIf($this->status === 'success')],
            'holder_name' => ['nullable', 'string', 'max:500', Rule::requiredIf($this->status === 'success')],
            'ai_raw_response' => ['nullable', 'array', Rule::requiredIf($this->status === 'success')],
            'error_message' => ['nullable', 'string', 'max:1000', Rule::requiredIf($this->status === 'failed')]
        ];
    }
}
