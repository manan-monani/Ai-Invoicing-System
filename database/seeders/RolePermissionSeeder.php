<?php

namespace Database\Seeders;

use App\Constants\Permissions;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        Model::withoutEvents(function () {
            // 1. Seed Permissions
            $sections = Permissions::getAll();
            foreach ($sections as $section) {
                foreach ($section['sub_modules'] as $permissionKey => $details) {
                    Permission::updateOrCreate(['name' => $permissionKey]);
                }
            }

            // 2. Create Roles
            $roles = [
                [
                    'name' => 'Owner',
                    'description' => 'Full access to all modules and settings.',
                ],
                [
                    'name' => 'Store Manager',
                    'description' => 'Manages invoices, customers, and catalog.',
                ],
                [
                    'name' => 'Lead Technician',
                    'description' => 'Handles repairs and service operations.',
                ],
                [
                    'name' => 'Sales Associate',
                    'description' => 'Manages customer invoices and sales.',
                ],
                [
                    'name' => 'Inventory Clerk',
                    'description' => 'Maintains catalog and stock listings.',
                ],
            ];

            foreach ($roles as $roleData) {
                $roleData['slug'] = Str::slug($roleData['name']);
                Role::updateOrCreate(
                    ['name' => $roleData['name']],
                    $roleData
                );
            }

            // 3. Assign Permissions to Roles
            $allPermissions = Permission::all();

            $ownerRole = Role::where('name', 'Owner')->first();
            if ($ownerRole) {
                $ownerRole->permissions()->sync($allPermissions->pluck('id'));
            }

            $rolePermissions = [
                'Store Manager' => [
                    Permissions::DASHBOARD_VIEW,
                    Permissions::INVOICES,
                    Permissions::CATALOG_ITEMS,
                    Permissions::CATALOG_CATEGORIES,
                    Permissions::CATALOG_TAXES,
                    Permissions::CUSTOMER_DIRECTORY,
                    Permissions::PAYMENT_METHODS,
                ],
                'Lead Technician' => [
                    Permissions::DASHBOARD_VIEW,
                    Permissions::INVOICES,
                    Permissions::CATALOG_ITEMS,
                ],
                'Sales Associate' => [
                    Permissions::DASHBOARD_VIEW,
                    Permissions::INVOICES,
                    Permissions::CUSTOMER_DIRECTORY,
                ],
                'Inventory Clerk' => [
                    Permissions::DASHBOARD_VIEW,
                    Permissions::CATALOG_ITEMS,
                    Permissions::CATALOG_CATEGORIES,
                ],
            ];

            foreach ($rolePermissions as $roleName => $permissionKeys) {
                $role = Role::where('name', $roleName)->first();
                if (! $role) {
                    continue;
                }

                $permissions = $allPermissions->filter(fn ($permission) => in_array($permission->name, $permissionKeys, true));
                $role->permissions()->sync($permissions->pluck('id'));
            }
        });
    }
}
