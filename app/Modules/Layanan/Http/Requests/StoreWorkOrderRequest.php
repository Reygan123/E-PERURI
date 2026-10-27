<?php

declare(strict_types=1);

namespace App\Modules\Layanan\Http\Requests;

use App\Modules\Layanan\Models\DeviceType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreWorkOrderRequest extends FormRequest
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
            'section_name' => ['required', 'string', 'max:150'],
            'device_type_id' => ['required', 'uuid'],
            'device_name' => ['required', 'string', 'max:150'],
            'it_inventory_code' => ['nullable', 'string', 'max:100'],
            'issue_description' => ['required', 'string', 'min:5'],
            'reference_memo_number' => ['nullable', 'string', 'max:100'],
            'photo' => ['nullable', 'image', 'max:5120'], // maks 5MB
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($this->device_type_id && ! DeviceType::where('id', $this->device_type_id)->exists()) {
                $validator->errors()->add('device_type_id', 'Kategori tipe peralatan tidak valid.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'section_name.required' => 'Nama seksi atau lokasi ruangan wajib diisi.',
            'device_type_id.required' => 'Pilih kategori tipe peralatan IT.',
            'device_type_id.exists' => 'Kategori tipe peralatan tidak valid.',
            'device_name.required' => 'Nama atau merek peralatan wajib diisi.',
            'issue_description.required' => 'Uraikan keluhan atau gejala kerusakan peralatan.',
            'issue_description.min' => 'Uraian kerusakan minimal 5 karakter.',
            'photo.image' => 'Berkas bukti kerusakan harus berupa gambar (JPG, PNG).',
            'photo.max' => 'Ukuran foto bukti kerusakan maksimal 5 MB.',
        ];
    }
}
