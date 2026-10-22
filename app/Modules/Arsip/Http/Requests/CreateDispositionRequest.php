<?php

declare(strict_types=1);

namespace App\Modules\Arsip\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateDispositionRequest extends FormRequest
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
            'recipient_employee_id' => ['nullable', 'uuid', 'required_without:recipient_unit_id'],
            'recipient_unit_id' => ['nullable', 'uuid', 'required_without:recipient_employee_id'],
            'instruction' => ['required', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'deadline' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'instruction.required' => 'Instruksi disposisi wajib diisi.',
            'recipient_employee_id.required_without' => 'Tujuan disposisi wajib dipilih (pegawai atau unit).',
            'recipient_unit_id.required_without' => 'Tujuan disposisi wajib dipilih (pegawai atau unit).',
        ];
    }
}
