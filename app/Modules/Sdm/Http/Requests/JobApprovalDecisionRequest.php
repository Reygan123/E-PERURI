<?php

declare(strict_types=1);

namespace App\Modules\Sdm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobApprovalDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'stage' => ['required', 'string', 'in:checker,approver,hrd_ratification,sdm_kadep'],
            'decision' => ['required', 'string', 'in:approved,rejected'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
