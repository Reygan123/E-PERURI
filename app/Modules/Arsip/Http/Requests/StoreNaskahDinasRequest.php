<?php

declare(strict_types=1);

namespace App\Modules\Arsip\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNaskahDinasRequest extends FormRequest
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
            'type_code' => ['required', 'in:ND,E,ST,PENG,KPTS'],
            'subject' => ['required', 'string', 'max:255'],
            'origin_unit_id' => ['required', 'uuid'],
            'destination_unit_ids' => ['nullable', 'array'],
            'destination_unit_ids.*' => ['uuid'],
            'letter_date' => ['required', 'date'],
            'confidentiality' => ['required', 'in:biasa,terbatas,rahasia,sangat_rahasia'],
            'urgency' => ['required', 'in:biasa,segera,sangat_segera'],
            'content_body' => ['nullable', 'array'],
            'draft_file' => ['nullable', 'file', 'mimes:pdf,docx,doc', 'max:10240'],
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
            'type_code.required' => 'Jenis naskah dinas wajib dipilih.',
            'subject.required' => 'Perihal naskah dinas wajib diisi.',
            'origin_unit_id.required' => 'Unit pembuat wajib dipilih.',
            'letter_date.required' => 'Tanggal naskah dinas wajib diisi.',
        ];
    }
}
