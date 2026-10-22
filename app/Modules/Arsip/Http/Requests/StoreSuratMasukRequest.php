<?php

declare(strict_types=1);

namespace App\Modules\Arsip\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSuratMasukRequest extends FormRequest
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
            'document_number' => ['required', 'string', 'max:100'],
            'origin_unit_id' => ['required', 'uuid'],
            'sender_organization' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'letter_date' => ['required', 'date'],
            'received_date' => ['required', 'date'],
            'confidentiality' => ['required', 'in:biasa,terbatas,rahasia,sangat_rahasia'],
            'urgency' => ['required', 'in:biasa,segera,sangat_segera'],
            'scanned_file' => ['required', 'file', 'mimes:pdf', 'max:15360'], // Max 15MB
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
            'document_number.required' => 'Nomor surat masuk dari pihak pengirim wajib diisi.',
            'origin_unit_id.required' => 'Unit penerima surat wajib dipilih.',
            'sender_organization.required' => 'Instansi / pihak pengirim wajib diisi.',
            'subject.required' => 'Perihal surat wajib diisi.',
            'scanned_file.required' => 'Berkas scan surat masuk wajib diunggah.',
            'scanned_file.mimes' => 'Berkas scan surat masuk harus berformat PDF.',
        ];
    }
}
