<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Modules\Platform\Contracts\AuditServiceInterface;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
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
            'email' => ['required_without_all:username,login', 'nullable', 'string'],
            'username' => ['required_without_all:email,login', 'nullable', 'string'],
            'login' => ['required_without_all:email,username', 'nullable', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $loginValue = (string) ($this->input('login') ?: ($this->input('username') ?: $this->input('email')));
        $isEmail = filter_var($loginValue, FILTER_VALIDATE_EMAIL) !== false;
        $credentialKey = $isEmail ? 'email' : 'username';

        $credentials = [
            $credentialKey => $loginValue,
            'password' => (string) $this->input('password'),
        ];

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            // Audit failed login
            app(AuditServiceInterface::class)->log(
                module: 'platform',
                action: 'LOGIN_FAILED',
                auditableType: 'User',
                auditableId: (string) Str::uuid(),
                oldValues: null,
                newValues: ['login' => $loginValue, 'ip' => $this->ip()],
                notes: 'Autentikasi gagal: kredensial tidak sesuai'
            );

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        /** @var User $user */
        $user = Auth::user();
        $user->update(['last_login_at' => now()]);

        // Audit successful login
        app(AuditServiceInterface::class)->log(
            module: 'platform',
            action: 'LOGIN_SUCCESS',
            auditableType: 'User',
            auditableId: $user->id,
            oldValues: null,
            newValues: ['login' => $loginValue, 'ip' => $this->ip()],
            notes: 'Autentikasi berhasil'
        );
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        $loginValue = (string) ($this->input('login') ?: ($this->input('username') ?: $this->input('email')));

        return Str::transliterate(Str::lower($loginValue).'|'.$this->ip());
    }
}
