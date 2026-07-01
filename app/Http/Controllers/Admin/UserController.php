<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Mail\EmployeeAccessMail;
use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function index(): \Inertia\Response
    {
        $this->authorizeDirectory();

        return $this->renderDirectory(null, __('Member Directory'), __('Manage member identities and access levels.'), route('admin.users.index'));
    }

    public function employees(): \Inertia\Response
    {
        \Illuminate\Support\Facades\Gate::authorize(\App\Constants\Permissions::EMPLOYEE_DIRECTORY);

        return $this->renderDirectory(UserType::EMPLOYEE, __('Employees'), __('Manage your employee accounts.'), route('admin.users.employees.index'));
    }

    public function customers(): \Inertia\Response
    {
        \Illuminate\Support\Facades\Gate::authorize(\App\Constants\Permissions::CUSTOMER_DIRECTORY);

        return $this->renderDirectory(UserType::CLIENT, __('Customers'), __('Manage your customer accounts.'), route('admin.users.customers.index'));
    }

    public function create(): \Inertia\Response
    {
        $this->authorizeDirectory();

        return inertia('Admin/Members/Create', [
            'roles' => Role::query()->employeeAssignable()->get(),
        ]);
    }

    public function store(StoreUserRequest $request): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeDirectory();

        $data = $request->validated();
        $password = Str::password(12);
        $data['password'] = $password;
        $data['type'] = UserType::EMPLOYEE;
        if (isset($data['role_id'])) {
            $data['roles'] = [$data['role_id']];
            unset($data['role_id']);
        }

        $user = $this->userService->create($data);
        Mail::to($user->email)->send(new EmployeeAccessMail($user, $password));

        return to_route('admin.users.index')->with('success', 'Employee created and login details emailed.');
    }

    public function edit(User $user): \Inertia\Response
    {
        $this->authorizeDirectory();

        $user->load('roles');
        $userData = $user->toArray();
        $userData['role_id'] = $user->roles->first()?->id ?? '';

        return inertia('Admin/Members/Edit', [
            'user' => $userData,
            'roles' => Role::query()->employeeAssignable()->get(),
        ]);
    }

    public function update(\App\Http\Requests\Admin\UpdateUserRequest $request, User $user): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeDirectory();

        $validated = $request->validated();

        if (isset($validated['role_id'])) {
            $validated['roles'] = [$validated['role_id']];
            unset($validated['role_id']);
        }

        // Remove password_confirmation
        unset($validated['password_confirmation']);

        $this->userService->update($user, $validated);

        return to_route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function updateStatus(\App\Http\Requests\Admin\UpdateUserStatusRequest $request, User $user): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeDirectory();

        $this->userService->updateStatus($user, $request->validated()['status']);

        return back()->with('success', 'User status updated successfully.');
    }

    public function destroy(User $user): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeDirectory();

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot delete your own account.');
        }

        $this->userService->delete($user);

        return back()->with('success', 'User deleted successfully.');
    }

    private function renderDirectory(?UserType $type, string $title, string $subtitle, string $indexUrl): \Inertia\Response
    {
        return inertia('Admin/Members/Index', [
            'users' => $this->userService->getAll(request()->only('search') + ['type' => $type?->value]),
            'roles' => Role::query()->employeeAssignable()->get(),
            'pageTitle' => $title,
            'pageSubtitle' => $subtitle,
            'indexUrl' => $indexUrl,
            'searchPlaceholder' => $type === UserType::CLIENT ? __('Search customers...') : ($type === UserType::EMPLOYEE ? __('Search employees...') : __('Search members...')),
            'createLabel' => $type === UserType::CLIENT ? null : ($type === UserType::EMPLOYEE ? __('Add Employee') : __('Add New Member')),
        ]);
    }

    private function authorizeDirectory(): void
    {
        if (! \Illuminate\Support\Facades\Gate::any([\App\Constants\Permissions::EMPLOYEE_DIRECTORY, \App\Constants\Permissions::CUSTOMER_DIRECTORY])) {
            \Illuminate\Support\Facades\Gate::authorize(\App\Constants\Permissions::EMPLOYEE_DIRECTORY);
        }
    }
}
