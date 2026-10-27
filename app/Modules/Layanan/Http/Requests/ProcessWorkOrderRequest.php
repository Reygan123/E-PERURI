<?php

declare(strict_types=1);

namespace App\Modules\Layanan\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProcessWorkOrderRequest extends FormRequest
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
            'examination_result' => ['nullable', 'string', 'in:repaired_internally,third_party_vendor,part_replacement,unrepairable'],
            'estimated_duration' => ['nullable', 'string', 'max:100'],
            'technician_notes' => ['nullable', 'string'],
            'take_ticket' => ['nullable', 'boolean'],
            'is_completed' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', 'in:pending,in_progress,completed,cancelled'],
        ];
    }
}
