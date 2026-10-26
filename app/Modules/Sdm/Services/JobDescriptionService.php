<?php

declare(strict_types=1);

namespace App\Modules\Sdm\Services;

use App\Models\User;
use App\Modules\Platform\Contracts\AuditServiceInterface;
use App\Modules\Platform\Contracts\DirectoryServiceInterface;
use App\Modules\Platform\Contracts\NotificationServiceInterface;
use App\Modules\Platform\Contracts\NumberingServiceInterface;
use App\Modules\Platform\Contracts\UserResolverInterface;
use App\Modules\Sdm\Models\JobApproval;
use App\Modules\Sdm\Models\JobCompetency;
use App\Modules\Sdm\Models\JobDescription;
use App\Modules\Sdm\Models\JobGovernance;
use App\Modules\Sdm\Models\JobSpecification;
use App\Modules\Sdm\Models\JobStandard;
use App\Modules\Sdm\Models\JobTask;
use App\Modules\Sdm\Models\JobWorkingRelation;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JobDescriptionService
{
    public function __construct(
        protected DirectoryServiceInterface $directory,
        protected NumberingServiceInterface $numbering,
        protected AuditServiceInterface $audit,
        protected NotificationServiceInterface $notifications,
        protected UserResolverInterface $userResolver
    ) {}

    /**
     * Get paginated katalog direktori jabatan ala Telkom University.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getKatalogList(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = JobDescription::query()
            ->select('sdm.job_descriptions.*');

        if (! empty($filters['search'])) {
            $search = '%'.strtolower((string) $filters['search']).'%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(job_summary) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(document_number) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(grade_recommendation) LIKE ?', [$search]);
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['org_unit_id'])) {
            $query->where('org_unit_id', $filters['org_unit_id']);
        }

        if (! empty($filters['job_group'])) {
            $query->where('job_group', $filters['job_group']);
        }

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Transform collection to attach directory metadata
        $paginator->getCollection()->transform(function ($job) {
            /** @var JobDescription $job */
            $unit = $this->directory->getOrgUnit($job->org_unit_id);
            $position = DB::table('platform.positions')->where('id', $job->position_id)->first();
            $employee = $job->employee_id ? $this->directory->getEmployeeById($job->employee_id) : null;

            return [
                'id' => $job->id,
                'document_number' => $job->document_number ?? 'DRAF-BELUM-TERBIT',
                'revision_number' => $job->revision_number,
                'sotk_reference' => $job->sotk_reference,
                'position_id' => $job->position_id,
                'position_name' => $position->name ?? 'Posisi Terdaftar',
                'position_code' => $position->code ?? '-',
                'job_group' => $job->job_group,
                'grade_recommendation' => $job->grade_recommendation,
                'org_unit_id' => $job->org_unit_id,
                'org_unit_name' => is_array($unit) ? ($unit['name'] ?? 'Unit Kerja') : 'Unit Kerja',
                'org_unit_code' => is_array($unit) ? ($unit['code'] ?? '-') : '-',
                'employee_name' => is_array($employee) ? ($employee['name'] ?? $employee['full_name'] ?? 'Pegawai Terdaftar') : ($job->employee_id ? 'Pegawai Terdaftar' : 'Belum Ditugaskan'),
                'status' => $job->status,
                'tte_hash' => $job->tte_hash,
                'created_at' => $job->created_at?->toIso8601String(),
            ];
        });

        return $paginator;
    }

    /**
     * Get detailed job description data for show/edit views.
     *
     * @return array<string, mixed>|null
     */
    public function getJobDetail(string $id): ?array
    {
        $job = JobDescription::with([
            'tasks',
            'governances',
            'workingRelations',
            'competencies',
            'specification',
            'standards',
            'approvals',
        ])->find($id);

        if (! $job) {
            return null;
        }

        $unit = $this->directory->getOrgUnit($job->org_unit_id);
        $position = DB::table('platform.positions')->where('id', $job->position_id)->first();
        $superiorPosition = $job->direct_superior_id ? DB::table('platform.positions')->where('id', $job->direct_superior_id)->first() : null;
        $employee = $job->employee_id ? $this->directory->getEmployeeById($job->employee_id) : null;

        // Parent unit lookup for hierarchical display (Direktorat / Departemen / Seksi)
        $parentId = is_array($unit) ? ($unit['parent_id'] ?? null) : null;
        $parentUnit = $parentId ? $this->directory->getOrgUnit($parentId) : null;
        $grandParentId = is_array($parentUnit) ? ($parentUnit['parent_id'] ?? null) : null;
        $grandParentUnit = $grandParentId ? $this->directory->getOrgUnit($grandParentId) : null;

        return [
            'id' => $job->id,
            'document_number' => $job->document_number ?? 'DRAF-BELUM-TERBIT',
            'revision_number' => $job->revision_number,
            'sotk_reference' => $job->sotk_reference,
            'status' => $job->status,
            'position_id' => $job->position_id,
            'position_name' => $position->name ?? 'Jabatan',
            'position_code' => $position->code ?? '-',
            'job_group' => $job->job_group,
            'grade_recommendation' => $job->grade_recommendation,
            'job_summary' => $job->job_summary,
            'direct_superior_id' => $job->direct_superior_id,
            'direct_superior_name' => $superiorPosition->name ?? 'Atasan Langsung',
            'subordinate_notes' => $job->subordinate_notes,
            'operating_challenges' => $job->operating_challenges,
            'environmental_conditions' => $job->environmental_conditions,
            'incidental_duties' => $job->incidental_duties,
            'tte_hash' => $job->tte_hash,
            'tte_signed_at' => $job->tte_signed_at?->toIso8601String(),
            'created_by' => $job->created_by,
            'created_at' => $job->created_at?->toIso8601String(),
            'org_unit' => [
                'id' => is_array($unit) ? ($unit['id'] ?? null) : null,
                'name' => is_array($unit) ? ($unit['name'] ?? 'Unit') : 'Unit',
                'code' => is_array($unit) ? ($unit['code'] ?? '-') : '-',
                'type' => is_array($unit) ? ($unit['type'] ?? 'department') : 'department',
                'parent_name' => is_array($parentUnit) ? ($parentUnit['name'] ?? null) : null,
                'grandparent_name' => is_array($grandParentUnit) ? ($grandParentUnit['name'] ?? null) : null,
            ],
            'employee' => is_array($employee) ? [
                'id' => $employee['id'] ?? null,
                'nip' => $employee['nip'] ?? '-',
                'name' => $employee['name'] ?? $employee['full_name'] ?? '-',
                'email' => $employee['email'] ?? '-',
            ] : null,
            'tasks' => $job->tasks->map(fn (JobTask $t) => [
                'id' => $t->id,
                'activity_name' => $t->activity_name,
                'activity_details' => $t->activity_details,
                'sort_order' => $t->sort_order,
            ])->toArray(),
            'governances' => [
                'responsibilities' => $job->governances->where('type', 'responsibility')->values()->map(fn (JobGovernance $g) => $g->description)->toArray(),
                'authorities' => $job->governances->where('type', 'authority')->values()->map(fn (JobGovernance $g) => $g->description)->toArray(),
            ],
            'working_relations' => [
                'internal' => $job->workingRelations->where('relation_type', 'internal')->values()->map(fn (JobWorkingRelation $r) => [
                    'id' => $r->id,
                    'entity' => $r->entity_name,
                    'matter' => $r->matter_description,
                ])->toArray(),
                'external' => $job->workingRelations->where('relation_type', 'external')->values()->map(fn (JobWorkingRelation $r) => [
                    'id' => $r->id,
                    'entity' => $r->entity_name,
                    'matter' => $r->matter_description,
                ])->toArray(),
            ],
            'competencies' => $job->competencies->map(fn (JobCompetency $c) => [
                'id' => $c->id,
                'sub_competency' => $c->sub_competency,
                'proficiency_level' => $c->proficiency_level,
                'key_indicator' => $c->key_indicator,
                'sort_order' => $c->sort_order,
            ])->toArray(),
            'specification' => $job->specification ? [
                'education_level' => $job->specification->education_level,
                'work_experience' => $job->specification->work_experience,
                'certifications' => $job->specification->certifications,
                'trainings' => $job->specification->trainings,
                'hard_skills' => $job->specification->hard_skills ?? [],
                'soft_skills' => $job->specification->soft_skills ?? [],
            ] : null,
            'standards' => $job->standards->map(fn (JobStandard $s) => [
                'id' => $s->id,
                'indicator_name' => $s->indicator_name,
                'target_value' => $s->target_value,
                'sort_order' => $s->sort_order,
            ])->toArray(),
            'approvals' => $job->approvals->map(fn (JobApproval $a) => [
                'id' => $a->id,
                'stage' => $a->stage,
                'role_title' => $a->role_title,
                'approver_name' => $a->approver_name,
                'decision' => $a->decision,
                'notes' => $a->notes,
                'decided_at' => $a->decided_at?->toIso8601String(),
            ])->toArray(),
        ];
    }

    /**
     * Store new job description draft.
     *
     * @param  array<string, mixed>  $data
     */
    public function storeJob(array $data, User $author): JobDescription
    {
        return DB::transaction(function () use ($data, $author) {
            $job = JobDescription::create([
                'id' => (string) Str::uuid(),
                'position_id' => $data['position_id'],
                'org_unit_id' => $data['org_unit_id'],
                'employee_id' => $data['employee_id'] ?? null,
                'document_number' => null,
                'revision_number' => 0,
                'sotk_reference' => $data['sotk_reference'] ?? 'SK 009/KPTS/PTKP/IV/2026',
                'job_group' => $data['job_group'] ?? 'Operasional & Manufaktur',
                'grade_recommendation' => $data['grade_recommendation'] ?? 'Grade 10',
                'job_summary' => $data['job_summary'],
                'direct_superior_id' => $data['direct_superior_id'] ?? null,
                'subordinate_notes' => $data['subordinate_notes'] ?? null,
                'operating_challenges' => $data['operating_challenges'] ?? null,
                'environmental_conditions' => $data['environmental_conditions'] ?? null,
                'incidental_duties' => $data['incidental_duties'] ?? null,
                'status' => 'draft',
                'created_by' => $author->id,
            ]);

            $this->syncJobDetails($job, $data);

            // Create initial creator approval stage
            JobApproval::create([
                'job_description_id' => $job->id,
                'stage' => 'maker',
                'role_title' => 'Pembuat / Pengusul Jabatan',
                'approver_user_id' => $author->id,
                'approver_name' => $author->name,
                'decision' => 'approved',
                'notes' => 'Draft awal dibuat dan disimpan.',
                'decided_at' => now(),
            ]);

            $this->audit->log(
                module: 'sdm',
                action: 'CREATE_JOB_DESCRIPTION',
                auditableType: 'JobDescription',
                auditableId: $job->id,
                newValues: ['position_id' => $job->position_id, 'org_unit_id' => $job->org_unit_id, 'status' => 'draft']
            );

            return $job;
        });
    }

    /**
     * Update job description.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateJob(JobDescription $job, array $data, User $editor): JobDescription
    {
        return DB::transaction(function () use ($job, $data) {
            $oldValues = $job->toArray();

            $job->update([
                'position_id' => $data['position_id'] ?? $job->position_id,
                'org_unit_id' => $data['org_unit_id'] ?? $job->org_unit_id,
                'employee_id' => $data['employee_id'] ?? $job->employee_id,
                'job_group' => $data['job_group'] ?? $job->job_group,
                'grade_recommendation' => $data['grade_recommendation'] ?? $job->grade_recommendation,
                'job_summary' => $data['job_summary'] ?? $job->job_summary,
                'direct_superior_id' => $data['direct_superior_id'] ?? $job->direct_superior_id,
                'subordinate_notes' => $data['subordinate_notes'] ?? $job->subordinate_notes,
                'operating_challenges' => $data['operating_challenges'] ?? $job->operating_challenges,
                'environmental_conditions' => $data['environmental_conditions'] ?? $job->environmental_conditions,
                'incidental_duties' => $data['incidental_duties'] ?? $job->incidental_duties,
            ]);

            $this->syncJobDetails($job, $data);

            $this->audit->log(
                module: 'sdm',
                action: 'UPDATE_JOB_DESCRIPTION',
                auditableType: 'JobDescription',
                auditableId: $job->id,
                oldValues: $oldValues,
                newValues: $job->fresh()->toArray()
            );

            return $job;
        });
    }

    /**
     * Submit job description for review / approval workflow.
     */
    public function submitForApproval(JobDescription $job, User $submitter): JobDescription
    {
        return DB::transaction(function () use ($job) {
            $job->update(['status' => 'in_review']);

            // Create checker stage (Kepala Seksi / Verifikator)
            JobApproval::create([
                'job_description_id' => $job->id,
                'stage' => 'checker',
                'role_title' => 'Pemeriksa / Kepala Seksi',
                'approver_user_id' => null,
                'approver_name' => 'Kepala Seksi Terkait',
                'decision' => 'pending',
                'notes' => null,
                'decided_at' => null,
            ]);

            $this->audit->log(
                module: 'sdm',
                action: 'SUBMIT_JOB_APPROVAL',
                auditableType: 'JobDescription',
                auditableId: $job->id,
                notes: 'Diajukan untuk pemeriksaan verifikator seksi'
            );

            return $job;
        });
    }

    /**
     * Process approval decision (approve / reject).
     */
    public function processApproval(JobDescription $job, string $stage, string $decision, ?string $notes, User $approver): JobDescription
    {
        return DB::transaction(function () use ($job, $stage, $decision, $notes, $approver) {
            $approval = JobApproval::where('job_description_id', $job->id)
                ->where('stage', $stage)
                ->where('decision', 'pending')
                ->latest()
                ->first();

            if ($approval) {
                $approval->update([
                    'approver_user_id' => $approver->id,
                    'approver_name' => $approver->name,
                    'decision' => $decision,
                    'notes' => $notes,
                    'decided_at' => now(),
                ]);
            }

            if ($decision === 'rejected') {
                $job->update(['status' => 'draft']);

                $this->audit->log(
                    module: 'sdm',
                    action: 'REJECT_JOB_APPROVAL',
                    auditableType: 'JobDescription',
                    auditableId: $job->id,
                    notes: "Ditolak pada tahap {$stage}: {$notes}"
                );

                return $job;
            }

            // If approved, advance to next stage or finalize
            if ($stage === 'checker') {
                // Advance to approver (Kepala Departemen)
                JobApproval::create([
                    'job_description_id' => $job->id,
                    'stage' => 'approver',
                    'role_title' => 'Penyetuju / Kepala Departemen',
                    'approver_user_id' => null,
                    'approver_name' => 'Kepala Departemen Terkait',
                    'decision' => 'pending',
                    'notes' => null,
                ]);
            } elseif ($stage === 'approver' || $stage === 'sdm_kadep') {
                // Finalize approval: generate official document number & TTE digital digest
                $unit = $this->directory->getOrgUnit($job->org_unit_id);
                $unitCode = is_array($unit) ? ($unit['code'] ?? 'SDM') : 'SDM';

                $docNumber = $this->numbering->generateNumber('JD', $unitCode, [
                    'year' => (int) date('Y'),
                    'month' => (int) date('n'),
                ]);

                $tteHash = hash('sha256', "JOBDESK|{$job->id}|{$docNumber}|".now()->toIso8601String());

                $job->update([
                    'status' => 'approved',
                    'document_number' => $docNumber,
                    'tte_hash' => $tteHash,
                    'tte_signed_at' => now(),
                ]);
            }

            $this->audit->log(
                module: 'sdm',
                action: 'APPROVE_JOB_STAGE',
                auditableType: 'JobDescription',
                auditableId: $job->id,
                notes: "Disetujui pada tahap {$stage} oleh {$approver->name}"
            );

            return $job;
        });
    }

    /**
     * Synchronize sub-detail tables (tasks, governances, working relations, competencies, specs, standards).
     *
     * @param  array<string, mixed>  $data
     */
    protected function syncJobDetails(JobDescription $job, array $data): void
    {
        // 1. Tasks
        if (isset($data['tasks']) && is_array($data['tasks'])) {
            $job->tasks()->delete();
            foreach ($data['tasks'] as $index => $t) {
                if (! empty($t['activity_name'])) {
                    JobTask::create([
                        'job_description_id' => $job->id,
                        'activity_name' => $t['activity_name'],
                        'activity_details' => $t['activity_details'] ?? '-',
                        'sort_order' => $t['sort_order'] ?? $index,
                    ]);
                }
            }
        }

        // 2. Governances (Responsibilities & Authorities)
        if (isset($data['responsibilities']) && is_array($data['responsibilities'])) {
            $job->governances()->where('type', 'responsibility')->delete();
            foreach ($data['responsibilities'] as $index => $resp) {
                if (! empty($resp)) {
                    JobGovernance::create([
                        'job_description_id' => $job->id,
                        'type' => 'responsibility',
                        'description' => (string) $resp,
                        'sort_order' => $index,
                    ]);
                }
            }
        }

        if (isset($data['authorities']) && is_array($data['authorities'])) {
            $job->governances()->where('type', 'authority')->delete();
            foreach ($data['authorities'] as $index => $auth) {
                if (! empty($auth)) {
                    JobGovernance::create([
                        'job_description_id' => $job->id,
                        'type' => 'authority',
                        'description' => (string) $auth,
                        'sort_order' => $index,
                    ]);
                }
            }
        }

        // 3. Working Relations (Internal & External ala Telkom Univ)
        if (isset($data['working_relations']) && is_array($data['working_relations'])) {
            $job->workingRelations()->delete();

            if (isset($data['working_relations']['internal']) && is_array($data['working_relations']['internal'])) {
                foreach ($data['working_relations']['internal'] as $index => $rel) {
                    if (! empty($rel['entity'])) {
                        JobWorkingRelation::create([
                            'job_description_id' => $job->id,
                            'relation_type' => 'internal',
                            'entity_name' => $rel['entity'],
                            'matter_description' => $rel['matter'] ?? '-',
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            if (isset($data['working_relations']['external']) && is_array($data['working_relations']['external'])) {
                foreach ($data['working_relations']['external'] as $index => $rel) {
                    if (! empty($rel['entity'])) {
                        JobWorkingRelation::create([
                            'job_description_id' => $job->id,
                            'relation_type' => 'external',
                            'entity_name' => $rel['entity'],
                            'matter_description' => $rel['matter'] ?? '-',
                            'sort_order' => $index,
                        ]);
                    }
                }
            }
        }

        // 4. Competencies (Proficiency Level 1-5 ala Telkom Univ)
        if (isset($data['competencies']) && is_array($data['competencies'])) {
            $job->competencies()->delete();
            foreach ($data['competencies'] as $index => $comp) {
                if (! empty($comp['sub_competency'])) {
                    JobCompetency::create([
                        'job_description_id' => $job->id,
                        'sub_competency' => $comp['sub_competency'],
                        'proficiency_level' => (int) ($comp['proficiency_level'] ?? 1),
                        'key_indicator' => $comp['key_indicator'] ?? '-',
                        'sort_order' => $index,
                    ]);
                }
            }
        }

        // 5. Specification
        if (isset($data['specification']) && is_array($data['specification'])) {
            $specData = $data['specification'];
            JobSpecification::updateOrCreate(
                ['job_description_id' => $job->id],
                [
                    'education_level' => $specData['education_level'] ?? 'S1 / D4',
                    'work_experience' => $specData['work_experience'] ?? 'Minimal 3 tahun',
                    'certifications' => $specData['certifications'] ?? null,
                    'trainings' => $specData['trainings'] ?? null,
                    'hard_skills' => $specData['hard_skills'] ?? [],
                    'soft_skills' => $specData['soft_skills'] ?? [],
                ]
            );
        }

        // 6. Standards (KPI)
        if (isset($data['standards']) && is_array($data['standards'])) {
            $job->standards()->delete();
            foreach ($data['standards'] as $index => $std) {
                if (! empty($std['indicator_name'])) {
                    JobStandard::create([
                        'job_description_id' => $job->id,
                        'indicator_name' => $std['indicator_name'],
                        'target_value' => $std['target_value'] ?? '-',
                        'sort_order' => $index,
                    ]);
                }
            }
        }
    }
}
