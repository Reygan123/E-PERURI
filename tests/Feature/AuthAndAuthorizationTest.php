<?php

use App\Models\User;
use App\Modules\Platform\Models\Employee;
use App\Modules\Platform\Models\OrgUnit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed();
});

test('user can authenticate using username or email with audit logging', function () {
    // 1. Login with username
    $response = $this->post('/login', [
        'username' => 'superadmin',
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $superUser = User::where('username', 'superadmin')->firstOrFail();
    expect($superUser->last_login_at)->not->toBeNull();

    $auditSuccess = DB::table('platform.audit_logs')
        ->where('action', 'LOGIN_SUCCESS')
        ->where('user_id', $superUser->id)
        ->latest('created_at')
        ->first();

    expect($auditSuccess)->not->toBeNull();

    // 2. Logout
    $this->post('/logout');
    $this->assertGuest();

    // 3. Login with email
    $response = $this->post('/login', [
        'email' => 'superadmin@kertas-padalarang.co.id',
        'password' => 'password',
    ]);
    $this->assertAuthenticated();

    // 4. Failed login
    $this->post('/logout');
    $response = $this->post('/login', [
        'username' => 'superadmin',
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
    $auditFailed = DB::table('platform.audit_logs')
        ->where('action', 'LOGIN_FAILED')
        ->latest('created_at')
        ->first();
    expect($auditFailed)->not->toBeNull();
});

test('user with must_change_pwd true is redirected to mandatory change password', function () {
    $user = User::where('username', 'operatorpk1')->firstOrFail();
    $user->update(['must_change_pwd' => true]);

    $this->actingAs($user);

    // Trying to access dashboard should redirect
    $response = $this->get('/dashboard');
    $response->assertRedirect(route('password.mandatory'));

    // Submitting new password
    $response = $this->post(route('password.mandatory.update'), [
        'current_password' => 'password',
        'password' => 'NewSecretPassword123!',
        'password_confirmation' => 'NewSecretPassword123!',
    ]);

    $response->assertRedirect(route('dashboard'));

    $user->refresh();
    expect($user->must_change_pwd)->toBeFalse()
        ->and(Hash::check('NewSecretPassword123!', $user->password))->toBeTrue();
});

test('authorization policies enforce scope correctly for all, unit_tree, and own', function () {
    $superadmin = User::where('username', 'superadmin')->firstOrFail();
    $kasiePk1 = User::where('username', 'kasiepk1')->firstOrFail();
    $operatorPk1 = User::where('username', 'operatorpk1')->firstOrFail();

    $empDirut = Employee::where('nip', 'PTKP-001')->firstOrFail();
    $empOperator = Employee::where('nip', 'PTKP-021')->firstOrFail();

    $unitPk1 = OrgUnit::where('code', 'PRD-PK1')->firstOrFail();
    $unitDirut = OrgUnit::where('code', 'DIR-UTM')->firstOrFail();

    // Superadmin (scope: all)
    expect(Gate::forUser($superadmin)->allows('view', $empDirut))->toBeTrue()
        ->and(Gate::forUser($superadmin)->allows('view', $empOperator))->toBeTrue()
        ->and(Gate::forUser($superadmin)->allows('view', $unitPk1))->toBeTrue();

    // Kasie PK1 (scope: unit_tree on PRD-PK1)
    expect(Gate::forUser($kasiePk1)->allows('view', $empOperator))->toBeTrue()
        ->and(Gate::forUser($kasiePk1)->allows('view', $unitPk1))->toBeTrue()
        ->and(Gate::forUser($kasiePk1)->allows('view', $empDirut))->toBeFalse();

    // Operator PK1 (scope: own)
    expect(Gate::forUser($operatorPk1)->allows('view', $empOperator))->toBeTrue()
        ->and(Gate::forUser($operatorPk1)->allows('view', $empDirut))->toBeFalse();
});
