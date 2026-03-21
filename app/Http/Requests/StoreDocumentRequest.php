<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('sanctum')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subcontractor_id' => ['required', 'uuid', 'exists:subcontractors,id'],
            'document_type' => ['required', 'string', 'in:coi,license,w9'],
            'file_url' => ['nullable', 'string', 'max:2000'],
            'uploaded_by' => ['nullable', 'string', 'email:rfc', 'max:255']
        ];
    }
}
