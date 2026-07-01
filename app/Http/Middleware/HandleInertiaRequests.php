<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $permissions = [];

        if ($user) {
            if ($user->isAdmin()) {
                $permissions = ['*'];
            } else {
                // Direct query to bypass Eloquent relationship loading issues
                $permissions = \Illuminate\Support\Facades\DB::table('user_roles')
                    ->join('role_permissions', 'user_roles.role_id', '=', 'role_permissions.role_id')
                    ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
                    ->where('user_roles.user_id', $user->id)
                    ->distinct()
                    ->pluck('permissions.name')
                    ->all();
            }
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'app' => [
                'mode' => config('app.mode'),
            ],
            'auth' => [
                'user' => $user,
                'is_super_admin' => $user?->isAdmin() ?? false,
                'permissions' => $permissions,
                'available_permissions' => collect(\App\Constants\Permissions::getAll())->map(function ($section) use ($user) {
                    if (isset($section['sub_modules'])) {
                        $section['sub_modules'] = collect($section['sub_modules'])->map(function ($module) use ($user) {
                            if (isset($module['route'])) {
                                try {
                                    $routeName = $module['route'];

                                    if ($user?->isEmployee() && Str::startsWith($routeName, 'admin.')) {
                                        $employeeRoute = 'employee.'.Str::after($routeName, 'admin.');

                                        if (Route::has($employeeRoute)) {
                                            $routeName = $employeeRoute;
                                        }
                                    }

                                    $module['url'] = route($routeName);
                                } catch (\Exception $e) {
                                    $module['url'] = '#'; // Fallback to avoid crash
                                }
                            }

                            return $module;
                        })->toArray();
                    }

                    return $section;
                })->toArray(),
            ],
            'branding' => array_merge(config('branding.theme'), [
                'business_settings' => \App\Models\BusinessSetting::all()->pluck('value', 'key')->toArray(),
            ]),
            'locale' => app()->getLocale(),
            'translations' => file_exists(lang_path(app()->getLocale().'.json'))
                ? json_decode(file_get_contents(lang_path(app()->getLocale().'.json')), true)
                : [],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
        ];
    }
}
