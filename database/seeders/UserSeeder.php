<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\Brand;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake();
        $password = Hash::make('12345678');
        $brandId = Brand::query()->value('id');
        $roles = Role::query()->employeeAssignable()->get();

        $departments = ['Repair Bench', 'Front Desk', 'Sales Floor', 'Inventory', 'Service Desk', 'Management'];
        $designations = ['Technician', 'Service Advisor', 'Cashier', 'Inventory Specialist', 'Store Manager', 'Lead Tech'];
        $cities = ['Austin', 'Dallas', 'Houston', 'San Antonio', 'Denver', 'Phoenix', 'Seattle'];

        Model::withoutEvents(function () use ($faker, $password, $brandId, $roles, $departments, $designations, $cities) {
            $admin = User::firstOrCreate(
                ['email' => 'admin@example.com'],
                [
                    'name' => 'Store Admin',
                    'password' => $password,
                    'type' => UserType::ADMIN,
                    'brand_id' => $brandId,
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            $admin->adminProfile()->updateOrCreate(
                ['user_id' => $admin->id],
                [
                    'employee_id' => 'ADM-0001',
                    'department' => 'Management',
                    'designation' => 'Owner',
                    'phone' => '+1 (512) 555-0100',
                ]
            );

            $ownerRole = $roles->firstWhere('name', 'Owner');
            if ($ownerRole) {
                $admin->roles()->sync([$ownerRole->id]);
            }

            $employee = User::firstOrCreate(
                ['email' => 'employee@example.com'],
                [
                    'name' => 'Riley Technician',
                    'password' => $password,
                    'type' => UserType::EMPLOYEE,
                    'brand_id' => $brandId,
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            $employee->adminProfile()->updateOrCreate(
                ['user_id' => $employee->id],
                [
                    'employee_id' => 'EMP-0001',
                    'department' => 'Repair Bench',
                    'designation' => 'Lead Technician',
                    'phone' => '+1 (512) 555-0123',
                ]
            );

            $managerRole = $roles->firstWhere('name', 'Store Manager') ?? $roles->first();
            if ($managerRole) {
                $employee->roles()->sync([$managerRole->id]);
            }

            $customer = User::firstOrCreate(
                ['email' => 'customer@example.com'],
                [
                    'name' => 'Jordan Customer',
                    'password' => $password,
                    'type' => UserType::CLIENT,
                    'brand_id' => $brandId,
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            $customer->customerProfile()->updateOrCreate(
                ['user_id' => $customer->id],
                [
                    'phone' => '+1 (512) 555-0199',
                    'address' => '4120 Burnet Rd',
                    'city' => 'Austin',
                ]
            );

            $employeeCount = 18;
            for ($i = 0; $i < $employeeCount; $i++) {
                $user = User::create([
                    'name' => $faker->name(),
                    'email' => $faker->unique()->safeEmail(),
                    'password' => $password,
                    'type' => UserType::EMPLOYEE,
                    'brand_id' => $brandId,
                    'status' => $faker->boolean(92) ? 'active' : 'inactive',
                    'email_verified_at' => now(),
                ]);

                $user->adminProfile()->create([
                    'employee_id' => strtoupper($faker->bothify('EMP-####')),
                    'department' => $faker->randomElement($departments),
                    'designation' => $faker->randomElement($designations),
                    'phone' => $faker->phoneNumber(),
                ]);

                if ($roles->isNotEmpty()) {
                    $assignments = $roles->random($faker->numberBetween(1, min(2, $roles->count())));
                    $user->roles()->sync(collect($assignments)->pluck('id')->all());
                }
            }

            $customerCount = 80;
            for ($i = 0; $i < $customerCount; $i++) {
                $user = User::create([
                    'name' => $faker->name(),
                    'email' => $faker->unique()->safeEmail(),
                    'password' => $password,
                    'type' => UserType::CLIENT,
                    'brand_id' => $brandId,
                    'status' => $faker->boolean(95) ? 'active' : 'inactive',
                    'email_verified_at' => now(),
                ]);

                $user->customerProfile()->create([
                    'phone' => $faker->phoneNumber(),
                    'address' => $faker->streetAddress(),
                    'city' => $faker->randomElement($cities),
                ]);
            }
        });
    }
}
