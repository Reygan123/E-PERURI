<?php

declare(strict_types=1);

namespace App\Modules\Arsip\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DecideApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'action' => ['required', 'in:approve,review,revise,reject'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'passphrase' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'action.required' => 'Keputusan persetujuan wajib ditentukan.',
            'action.in' => 'Keputusan persetujuan tidak valid.',
        ];
    }
}
