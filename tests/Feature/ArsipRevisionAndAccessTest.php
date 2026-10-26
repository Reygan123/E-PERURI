<?php

use App\Models\User;
use App\Modules\Arsip\Database\Seeders\LetterTypeSeeder;
use App\Modules\Arsip\Models\Letter;
use App\Modules\Arsip\Models\LetterApproval;
use App\Modules\Arsip\Models\LetterType;
use App\Modules\Platform\Database\Seeders\DevelopmentUserSeeder;
use App\Modules\Platform\Database\Seeders\NumberingFormatSeeder;
use App\Modules\Platform\Database\Seeders\OrgUnitSeeder;
use App\Modules\Platform\Database\Seeders\RoleAndPermissionSeeder;
use App\Modules\Platform\Models\Employee;
use App\Modules\Platform\Models\OrgUnit;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => OrgUnitSeeder::class]);
    $this->artisan('db:seed', ['--class' => RoleAndPermissionSeeder::class]);
    $this->artisan('db:seed', ['--class' => DevelopmentUserSeeder::class]);
    $this->artisan('db:seed', ['--class' => NumberingFormatSeeder::class]);
    $this->artisan('db:seed', ['--class' => LetterTypeSeeder::class]);
});

test('staff and operator users cannot access platform core routes and receive 403', function () {
    $operator = User::where('username', 'operatorpk1')->firstOrFail();
    $superadmin = User::where('username', 'superadmin')->firstOrFail();

    // 1. Operator blocked by EnsurePlatformAdmin middleware from Platform Core
    $response = $this->actingAs($operator)->get(route('platform.org-units.index'));
    $response->assertForbidden();

    $response = $this->actingAs($operator)->get(route('platform.users.index'));
    $response->assertForbidden();

    $response = $this->actingAs($operator)->get(route('platform.positions.index'));
    $response->assertForbidden();

    // 2. Superadmin has full access to Platform Core
    $response = $this->actingAs($superadmin)->get(route('platform.org-units.index'));
    $response->assertOk();

    $response = $this->actingAs($superadmin)->get(route('platform.users.index'));
    $response->assertOk();
});

test('letter revision flow allows updating draft and resets approval steps back to step 1', function () {
    $operator = User::where('username', 'operatorpk1')->firstOrFail();
    $emp = Employee::findOrFail($operator->employee_id);
    $unit = OrgUnit::findOrFail($emp->activeAssignment->org_unit_id);
    $type = LetterType::where('code', 'ND')->firstOrFail();

    // 1. Create a draft Nota Dinas
    $letter = Letter::create([
        'id' => (string) Str::uuid(),
        'type_id' => $type->id,
        'origin_unit_id' => $unit->id,
        'creator_employee_id' => $emp->id,
        'destination_type' => 'internal_units',
        'subject' => 'Draf Nota Dinas Awal',
        'letter_date' => now()->toDateString(),
        'status' => 'draft',
        'confidentiality' => 'biasa',
        'urgency' => 'biasa',
        'content_body' => ['body_html' => '<p>Konten awal</p>'],
    ]);

    // 2. Submit for approval
    $this->actingAs($operator)->post(route('arsip.naskah-dinas.submit', $letter->id));
    $letter->refresh();
    expect($letter->status)->toBe('in_review');

    // 3. Approver asks for revision
    $firstApproval = LetterApproval::where('letter_id', $letter->id)->orderBy('step_order')->firstOrFail();
    $firstApproval->update([
        'status' => 'revision_requested',
        'notes' => 'Harap lengkapi latar belakang permohonan.',
    ]);
    $letter->update(['status' => 'revision_requested']);

    // 4. Creator accesses edit page
    $editPage = $this->actingAs($operator)->get(route('arsip.naskah-dinas.edit', $letter->id));
    $editPage->assertOk();

    // 5. Creator updates and resubmits the revised letter
    $updateResponse = $this->actingAs($operator)->put(route('arsip.naskah-dinas.update', $letter->id), [
        'type_code' => 'ND',
        'subject' => 'Draf Nota Dinas Revisi Final',
        'origin_unit_id' => $unit->id,
        'letter_date' => now()->toDateString(),
        'confidentiality' => 'biasa',
        'urgency' => 'biasa',
        'content_body' => [
            'body_html' => '<p>Konten setelah perbaikan latar belakang lengkap.</p>',
            'kepada' => 'Kepala Seksi Terkait',
        ],
        'submit_now' => true,
    ]);

    $updateResponse->assertRedirect(route('arsip.naskah-dinas.show', $letter->id));

    $letter->refresh();
    expect($letter->status)->toBe('in_review');
    expect($letter->subject)->toBe('Draf Nota Dinas Revisi Final');

    // 6. Approval workflow is reset back to step 1 (rule 52: alur diulang dari bawah)
    $activeSteps = LetterApproval::where('letter_id', $letter->id)->orderBy('step_order')->get();
    expect($activeSteps)->not->toBeEmpty();
    expect($activeSteps->first()->status)->toBe('pending');
});
