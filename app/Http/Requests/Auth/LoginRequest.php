<?php

namespace App\Http\Requests\Auth;

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
            'email' => ['required', 'string'],
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

        $loginInput = trim($this->input('email'));
        $password = (string) $this->input('password');
        $remember = $this->boolean('remember');

        // 1. Coba login lokal via Email
        $logged = Auth::attempt(['email' => $loginInput, 'password' => $password], $remember);

        // 2. Jika gagal, coba login lokal via Username / NRP
        if (!$logged) {
            $logged = Auth::attempt(['username' => $loginInput, 'password' => $password], $remember);
        }

        // 3. Jika belum berhasil (atau saat di Vercel / akun baru di Spreadsheet)
        // Autentikasi langsung ke tab 'Pengguna' di Google Spreadsheet
        if (!$logged) {
            try {
                $googleSheetService = app(\App\Services\GoogleSheetService::class);
                $sheetUser = $googleSheetService->authenticateFromSheet($loginInput, $password);
                if ($sheetUser) {
                    Auth::login($sheetUser, $remember);
                    $logged = true;
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Spreadsheet auth error: " . $e->getMessage());
            }
        }

        if (!$logged) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Email / NRP atau kata sandi yang Anda masukkan salah.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
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
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
