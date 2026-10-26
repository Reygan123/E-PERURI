<?php

declare(strict_types=1);

namespace App\Modules\Sdm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobDescriptionRequest extends FormRequest
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
            'position_id' => ['required', 'uuid'],
            'org_unit_id' => ['required', 'uuid'],
            'employee_id' => ['nullable', 'uuid'],
            'sotk_reference' => ['nullable', 'string', 'max:255'],
            'job_group' => ['required', 'string', 'max:100'],
            'grade_recommendation' => ['required', 'string', 'max:50'],
            'job_summary' => ['required', 'string', 'min:10'],
            'direct_superior_id' => ['nullable', 'uuid'],
            'subordinate_notes' => ['nullable', 'string'],
            'operating_challenges' => ['nullable', 'string'],
            'environmental_conditions' => ['nullable', 'string'],
            'incidental_duties' => ['nullable', 'string'],

            // Sub-details
            'tasks' => ['nullable', 'array'],
            'tasks.*.activity_name' => ['required_with:tasks', 'string', 'max:255'],
            'tasks.*.activity_details' => ['nullable', 'string'],
            'tasks.*.sort_order' => ['nullable', 'integer'],

            'responsibilities' => ['nullable', 'array'],
            'responsibilities.*' => ['nullable', 'string'],

            'authorities' => ['nullable', 'array'],
            'authorities.*' => ['nullable', 'string'],

            'working_relations' => ['nullable', 'array'],
            'working_relations.internal' => ['nullable', 'array'],
            'working_relations.internal.*.entity' => ['required_with:working_relations.internal', 'string', 'max:255'],
            'working_relations.internal.*.matter' => ['nullable', 'string'],
            'working_relations.external' => ['nullable', 'array'],
            'working_relations.external.*.entity' => ['required_with:working_relations.external', 'string', 'max:255'],
            'working_relations.external.*.matter' => ['nullable', 'string'],

            'competencies' => ['nullable', 'array'],
            'competencies.*.sub_competency' => ['required_with:competencies', 'string', 'max:255'],
            'competencies.*.proficiency_level' => ['required_with:competencies', 'integer', 'min:1', 'max:5'],
            'competencies.*.key_indicator' => ['nullable', 'string'],

            'specification' => ['nullable', 'array'],
            'specification.education_level' => ['nullable', 'string', 'max:255'],
            'specification.work_experience' => ['nullable', 'string', 'max:255'],
            'specification.certifications' => ['nullable', 'string', 'max:255'],
            'specification.trainings' => ['nullable', 'string', 'max:255'],
            'specification.hard_skills' => ['nullable', 'array'],
            'specification.soft_skills' => ['nullable', 'array'],

            'standards' => ['nullable', 'array'],
            'standards.*.indicator_name' => ['required_with:standards', 'string', 'max:255'],
            'standards.*.target_value' => ['nullable', 'string', 'max:255'],
        ];
    }
}
