<?php

declare(strict_types=1);

namespace App\Modules\Arsip\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSuratKeluarRequest extends FormRequest
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
            'subject' => ['required', 'string', 'max:255'],
            'origin_unit_id' => ['required', 'uuid'],
            'destination_type' => ['required', 'in:external_text,internal_units,all_units'],
            'destination_text' => ['nullable', 'string', 'max:255'],
            'destination_unit_ids' => ['nullable', 'array'],
            'destination_unit_ids.*' => ['uuid'],
            'letter_date' => ['required', 'date'],
            'confidentiality' => ['required', 'in:biasa,terbatas,rahasia,sangat_rahasia'],
            'urgency' => ['required', 'in:biasa,segera,sangat_segera'],
            'content_body' => ['nullable', 'array'],
            'draft_file' => ['nullable', 'file', 'mimes:pdf,docx,doc', 'max:10240'], // Max 10MB
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
            'subject.required' => 'Perihal surat wajib diisi.',
            'origin_unit_id.required' => 'Unit asal surat wajib dipilih.',
            'letter_date.required' => 'Tanggal surat wajib diisi.',
            'draft_file.mimes' => 'Berkas draf surat harus berformat PDF, DOCX, atau DOC.',
            'draft_file.max' => 'Ukuran berkas draf surat maksimal 10MB.',
        ];
    }
}
