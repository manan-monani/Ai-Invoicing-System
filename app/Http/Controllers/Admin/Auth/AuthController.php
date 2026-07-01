<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Constants\Permissions;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('Admin/Auth/Login');
    }

    public function storeLogin(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'captcha' => ['required', function ($attribute, $value, $fail) {
                if ($value != session('captcha_code')) {
                    $fail(__('The captcha is invalid.'));
                }
            }],
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            /** @var \App\Models\User $user */
            $user = Auth::user();
            if ($user->isClient()) {
                Auth::logout();

                return redirect()->back()->with('error', __('Customers must login via the Customer Portal.'));
            }

            // Ensure user has admin profile
            if (! $user->adminProfile) {
                $user->adminProfile()->create();
            }

            return $this->redirectToAdminHome($request, $user);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function showRegister(): Response
    {
        return Inertia::render('Admin/Auth/Register');
    }

    public function storeRegister(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'captcha' => ['required', function ($attribute, $value, $fail) {
                if ($value != session('captcha_code')) {
                    $fail(__('The captcha is invalid.'));
                }
            }],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => UserType::ADMIN,
        ]);

        $user->adminProfile()->create();

        Auth::login($user);

        return redirect(route('admin.dashboard'));
    }

    private function redirectToAdminHome(Request $request, User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        if ($user->isEmployee()) {
            return redirect()->route('employee.dashboard');
        }

        foreach (Permissions::getAll() as $sectionKey => $section) {
            $sectionLabel = strtolower($section['label'] ?? '');
            $normalizedSectionKey = strtolower((string) $sectionKey);

            if (str_contains($sectionLabel, 'system') || str_contains($normalizedSectionKey, 'system')) {
                continue;
            }

            if (! isset($section['sub_modules'])) {
                continue;
            }

            foreach ($section['sub_modules'] as $permissionName => $module) {
                if (! isset($module['route'])) {
                    continue;
                }

                if (Gate::forUser($user)->allows($permissionName)) {
                    return redirect()->route($module['route']);
                }
            }
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('admin.login')
            ->with('error', __('Your account does not have access to any admin modules.'));
    }
}
