<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake();
        $users = User::query()->get();
        $invoices = Invoice::query()->get();
        $items = Item::query()->get();
        $paymentMethods = PaymentMethod::query()->get();

        if ($users->isEmpty()) {
            return;
        }

        $subjects = [
            ['type' => Invoice::class, 'items' => $invoices],
            ['type' => Item::class, 'items' => $items],
            ['type' => PaymentMethod::class, 'items' => $paymentMethods],
            ['type' => User::class, 'items' => $users],
        ];

        $events = ['created', 'updated', 'deleted', 'login', 'logout', 'failed_login'];
        $describe = function (string $event, ?array $subject): string {
            return match ($event) {
                'login' => 'User signed in',
                'logout' => 'User signed out',
                'failed_login' => 'Failed login attempt',
                'created' => match ($subject['type'] ?? null) {
                    Invoice::class => 'Created a repair invoice',
                    Item::class => 'Added a new catalog item',
                    PaymentMethod::class => 'Added a payment method',
                    User::class => 'Created a customer or employee',
                    default => 'Created a new record',
                },
                'updated' => match ($subject['type'] ?? null) {
                    Invoice::class => 'Updated invoice totals',
                    Item::class => 'Updated item pricing',
                    PaymentMethod::class => 'Updated payment method',
                    User::class => 'Updated customer or employee profile',
                    default => 'Updated record details',
                },
                'deleted' => match ($subject['type'] ?? null) {
                    Invoice::class => 'Deleted an invoice',
                    Item::class => 'Removed a catalog item',
                    PaymentMethod::class => 'Removed a payment method',
                    User::class => 'Removed a user account',
                    default => 'Deleted a record',
                },
                default => 'System activity recorded',
            };
        };

        for ($i = 0; $i < 220; $i++) {
            $event = $faker->randomElement($events);
            $user = $faker->boolean(85) ? $users->random() : null;
            $subject = $faker->boolean(70) ? $faker->randomElement($subjects) : null;
            $subjectItem = $subject && $subject['items']->isNotEmpty() ? $subject['items']->random() : null;

            $properties = null;
            if ($event === 'updated') {
                $properties = [
                    'status' => ['old' => 'draft', 'new' => 'sent'],
                    'updated_by' => $user?->email,
                ];
            } elseif ($event === 'created') {
                $properties = ['created_by' => $user?->email];
            }

            ActivityLog::create([
                'user_id' => $user?->id,
                'subject_type' => $subjectItem ? $subject['type'] : null,
                'subject_id' => $subjectItem?->id,
                'event' => $event,
                'description' => $describe($event, $subject),
                'properties' => $properties,
                'ip_address' => $faker->ipv4,
                'user_agent' => $faker->userAgent,
                'created_at' => $faker->dateTimeBetween('-3 months', 'now'),
                'updated_at' => now(),
            ]);
        }
    }
}
