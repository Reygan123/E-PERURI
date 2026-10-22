<?php

declare(strict_types=1);

namespace App\Modules\Arsip\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSpecialDocumentRequest extends FormRequest
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
            'type_code' => ['required', 'in:SPPD,SPB,BP'],
            'subject' => ['required', 'string', 'max:255'],
            'origin_unit_id' => ['required', 'uuid'],
            'letter_date' => ['required', 'date'],
            'content_body' => ['required', 'array'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:pdf,docx,doc,xlsx,xls,png,jpg,jpeg', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type_code.required' => 'Jenis formulir khusus wajib dipilih.',
            'subject.required' => 'Perihal / uraian formulir wajib diisi.',
            'origin_unit_id.required' => 'Unit asal wajib dipilih.',
            'content_body.required' => 'Rincian data formulir wajib diisi.',
        ];
    }
}
