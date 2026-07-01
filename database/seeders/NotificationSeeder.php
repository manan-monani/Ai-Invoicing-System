<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake();
        $users = User::query()->get();
        $invoices = Invoice::query()->get()->groupBy('customer_id');

        if ($users->isEmpty()) {
            return;
        }

        $templates = [
            ['type' => 'info', 'title' => 'Repair status updated', 'description' => 'Your repair invoice has been updated with new details.'],
            ['type' => 'success', 'title' => 'Payment received', 'description' => 'Payment recorded for your repair invoice.'],
            ['type' => 'warning', 'title' => 'Pickup reminder', 'description' => 'Your device is ready for pickup.'],
            ['type' => 'error', 'title' => 'Payment failed', 'description' => 'We could not process a payment for this invoice.'],
        ];

        Model::withoutEvents(function () use ($faker, $users, $invoices, $templates) {
            foreach ($users as $user) {
                $count = $faker->numberBetween(2, 6);

                for ($i = 0; $i < $count; $i++) {
                    $template = $faker->randomElement($templates);
                    $userInvoices = $invoices->get($user->id, collect());
                    $invoice = $userInvoices->isNotEmpty() ? $userInvoices->random() : null;
                    $path = $user->type === UserType::CLIENT
                        ? ($invoice ? "/customer/invoices/{$invoice->id}" : '/customer/invoices')
                        : ($invoice ? "/admin/invoices/{$invoice->id}" : '/admin/invoices');

                    Notification::create([
                        'user_id' => $user->id,
                        'type' => $template['type'],
                        'title' => $template['title'],
                        'description' => $template['description'],
                        'data' => $invoice ? ['invoice_id' => $invoice->id] : null,
                        'action_url' => $path,
                        'read_at' => $faker->boolean(55) ? $faker->dateTimeBetween('-2 months', 'now') : null,
                        'created_at' => $faker->dateTimeBetween('-3 months', 'now'),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }
}
