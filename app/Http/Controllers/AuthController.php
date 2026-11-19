<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show login form.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Authenticate user with rate limiting and audit logging.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Nomor Identitas (NIS / NIP / Username) wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $throttleKey = Str::transliterate(Str::lower($credentials['username']).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'username' => "Terlalu banyak percobaan login gagal. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('username');
        }

        // Try username or email
        $user = User::where('username', $credentials['username'])
            ->orWhere('email', $credentials['username'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            AuditLog::record('login_failed', [
                'attempted_username' => $credentials['username'],
                'reason' => 'Invalid credentials',
            ]);

            return back()->withErrors([
                'username' => 'Identitas atau password yang Anda masukkan salah.',
            ])->onlyInput('username');
        }

        if (! $user->is_active) {
            AuditLog::record('login_blocked_inactive', ['user_id' => $user->id], $user->id);

            return back()->withErrors([
                'username' => 'Akun Anda dinonaktifkan oleh Administrator. Hubungi pihak sekolah.',
            ])->onlyInput('username');
        }

        RateLimiter::clear($throttleKey);
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        AuditLog::record('login_success', ['role' => $user->role], $user->id);

        if ($user->must_change_password) {
            return redirect()->route('password.change')
                ->with('info', 'Selamat datang! Karena ini adalah login pertama Anda, silakan ubah password awal Anda.');
        }

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Selamat datang kembali, '.$user->name.'!');
    }

    /**
     * Logout user.
     */
    public function logout(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            AuditLog::record('logout', ['role' => Auth::user()->role], Auth::id());
            Auth::logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }

    /**
     * Show change password form.
     */
    public function showChangePassword(): View
    {
        return view('auth.change-password');
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $rules = [
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ];

        // If not mandatory first change, verify old password
        if (! $user->must_change_password) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $request->validate($rules, [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal harus 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        AuditLog::record('password_changed', ['by_user' => true], $user->id);

        return redirect()->route('dashboard')
            ->with('success', 'Password berhasil diperbarui. Akun Anda kini aman.');
    }

    /**
     * User profile view.
     */
    public function profile(): View
    {
        $user = Auth::user()->load(['teacher', 'student.schoolClass']);

        return view('auth.profile', compact('user'));
    }

    /**
     * Update user profile information.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone_number' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update($validated);

        if ($user->teacher) {
            $user->teacher->update([
                'name' => $validated['name'],
                'phone' => $validated['phone_number'],
            ]);
        }

        if ($user->student) {
            $user->student->update([
                'name' => $validated['name'],
            ]);
        }

        AuditLog::record('profile_updated', ['fields' => array_keys($validated)], $user->id);

        return back()->with('success', 'Profil Anda berhasil diperbarui.');
    }
}
