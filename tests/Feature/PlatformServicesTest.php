<?php

use App\Models\User;
use App\Modules\Platform\Contracts\AuditServiceInterface;
use App\Modules\Platform\Contracts\DirectoryServiceInterface;
use App\Modules\Platform\Contracts\FilesServiceInterface;
use App\Modules\Platform\Contracts\NotificationServiceInterface;
use App\Modules\Platform\Contracts\NumberingServiceInterface;
use App\Modules\Platform\Contracts\UserResolverInterface;
use App\Modules\Platform\Models\Employee;
use App\Modules\Platform\Models\OrgUnit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed();
});

test('directory service queries employee details and unit hierarchy accurately', function () {
    /** @var DirectoryServiceInterface $directory */
    $directory = app(DirectoryServiceInterface::class);

    $employee = $directory->getEmployeeByNip('PTKP-021');
    expect($employee)->not->toBeNull()
        ->and($employee['nip'])->toBe('PTKP-021')
        ->and($employee['org_unit']['code'])->toBe('PRD-PK1');

    $dirOps = OrgUnit::where('code', 'DIR-OPS')->firstOrFail();
    $subordinateIds = $directory->getSubordinateOrgUnitIds($dirOps->id);
    expect($subordinateIds)->toBeArray()
        ->and(count($subordinateIds))->toBeGreaterThan(5);

    // Operator PK1 should be in PRD-PK1 and in DIR-OPS recursively
    $empModel = Employee::where('nip', 'PTKP-021')->firstOrFail();
    $secPk1 = OrgUnit::where('code', 'PRD-PK1')->firstOrFail();

    expect($directory->isEmployeeInOrgUnit($empModel->id, $secPk1->id, false))->toBeTrue()
        ->and($directory->isEmployeeInOrgUnit($empModel->id, $dirOps->id, true))->toBeTrue();
});

test('numbering service generates atomic sequential document numbers', function () {
    /** @var NumberingServiceInterface $numbering */
    $numbering = app(NumberingServiceInterface::class);

    $num1 = $numbering->generateNumber('SK', 'DIR-UTM', ['year' => 2026, 'month' => 4]);
    $num2 = $numbering->generateNumber('SK', 'DIR-UTM', ['year' => 2026, 'month' => 4]);

    expect($num1)->toContain('001/SK/PTKP/DIR-UTM/IV/2026')
        ->and($num2)->toContain('002/SK/PTKP/DIR-UTM/IV/2026');
});

test('files service stores files securely and verifies sha256 integrity', function () {
    /** @var FilesServiceInterface $filesService */
    $filesService = app(FilesServiceInterface::class);

    $fakeFile = UploadedFile::fake()->create('memo_internal.pdf', 100, 'application/pdf');

    $meta = $filesService->store($fakeFile, 'arsip', 'memo');
    expect($meta)->toHaveKeys(['id', 'disk', 'path', 'hash_sha256'])
        ->and($filesService->verifyIntegrity($meta['id']))->toBeTrue();
});

test('audit service logs immutable action entries with request metadata', function () {
    /** @var AuditServiceInterface $auditService */
    $auditService = app(AuditServiceInterface::class);

    $user = User::where('username', 'superadmin')->firstOrFail();
    $this->actingAs($user);

    $entityId = (string) Str::uuid();

    $auditService->log(
        module: 'platform',
        action: 'UPDATE',
        auditableType: 'OrgUnit',
        auditableId: $entityId,
        oldValues: ['name' => 'Old Unit'],
        newValues: ['name' => 'New Unit'],
        notes: 'Renamed unit'
    );

    $logEntry = DB::table('platform.audit_logs')->where('entity_id', $entityId)->first();
    expect($logEntry)->not->toBeNull()
        ->and($logEntry->action)->toBe('UPDATE')
        ->and($logEntry->module)->toBe('platform')
        ->and($logEntry->user_id)->toBe($user->id);
});

test('notification service dispatches database records for recipient employees', function () {
    /** @var NotificationServiceInterface $notifier */
    $notifier = app(NotificationServiceInterface::class);

    $emp = Employee::where('nip', 'PTKP-001')->firstOrFail();

    $notifier->send(
        recipientEmployeeIds: [$emp->id],
        title: 'Pengumuman Penting',
        message: 'Harap periksa memo internal terbaru.',
        channels: ['database']
    );

    $notif = DB::table('platform.notifications')->first();
    expect($notif)->not->toBeNull()
        ->and($notif->channel)->toBe('database');
});

test('user resolver resolves authenticated user roles, permissions, and scope', function () {
    /** @var UserResolverInterface $resolver */
    $resolver = app(UserResolverInterface::class);

    $superUser = User::where('username', 'superadmin')->firstOrFail();
    $this->actingAs($superUser);

    expect($resolver->getCurrentUserId())->toBe($superUser->id)
        ->and($resolver->getCurrentEmployeeId())->toBe($superUser->employee_id)
        ->and($resolver->hasRole('super-admin'))->toBeTrue()
        ->and($resolver->hasPermission('platform.org_unit.view'))->toBeTrue()
        ->and($resolver->getActiveScope()['scope'])->toBe('all');
});
