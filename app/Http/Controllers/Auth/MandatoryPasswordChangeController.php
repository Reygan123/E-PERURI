<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Platform\Contracts\AuditServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class MandatoryPasswordChangeController extends Controller
{
    /**
     * Display the mandatory password change view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/MandatoryChangePassword');
    }

    /**
     * Update the mandatory new password.
     */
    public function store(Request $request, AuditServiceInterface $auditService): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed', 'different:current_password'],
        ]);

        /** @var User $user */
        $user = $request->user();

        $user->update([
            'password' => Hash::make($validated['password']),
            'must_change_pwd' => false,
        ]);

        $auditService->log(
            module: 'platform',
            action: 'MANDATORY_PASSWORD_CHANGED',
            auditableType: 'User',
            auditableId: $user->id,
            oldValues: ['must_change_pwd' => true],
            newValues: ['must_change_pwd' => false],
            notes: 'User menyelesaikan pergantian password wajib'
        );

        return redirect()->route('dashboard')->with('status', 'Password berhasil diperbarui.');
    }
}
