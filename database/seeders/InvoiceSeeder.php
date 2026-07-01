<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\Item;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake();
        $customers = User::query()->where('type', UserType::CLIENT)->get();
        $items = Item::query()->with(['category', 'tax'])->get();
        $paymentMethods = PaymentMethod::query()->where('is_active', true)->get();

        if ($customers->isEmpty() || $items->isEmpty()) {
            return;
        }

        /** @var InvoiceService $invoiceService */
        $invoiceService = app(InvoiceService::class);
        $invoiceCount = 140;
        $notes = [
            'Device diagnostics completed; awaiting approval.',
            'Parts on order, expected arrival next week.',
            'Customer notified that device is ready for pickup.',
            'Rush repair requested for same-day turnaround.',
            'Data backup completed prior to service.',
            'Warranty coverage applied to selected parts.',
        ];

        Model::withoutEvents(function () use ($faker, $customers, $items, $paymentMethods, $invoiceService, $invoiceCount, $notes) {
            for ($i = 0; $i < $invoiceCount; $i++) {
                $customer = $customers->random();
                $issueDate = Carbon::instance($faker->dateTimeBetween('-12 months', 'now'));
                $dueDate = (clone $issueDate)->addDays($faker->numberBetween(7, 30));

                $selectedItems = $items->random($faker->numberBetween(1, 4));
                $lineItems = collect($selectedItems)->map(function (Item $item) use ($faker) {
                    $qty = $faker->numberBetween(1, 3);
                    $unitPrice = (float) $item->unit_price;
                    if ($faker->boolean(20)) {
                        $unitPrice = round($unitPrice * $faker->randomFloat(2, 0.9, 1.15), 2);
                    }

                    return [
                        'item_id' => $item->id,
                        'name' => $item->name,
                        'description' => $item->description,
                        'qty' => $qty,
                        'unit_price' => $unitPrice,
                    ];
                })->values()->all();

                $discount = $faker->boolean(15)
                    ? $faker->randomFloat(2, 5, 80)
                    : 0;

                $invoice = $invoiceService->create([
                    'customer_id' => $customer->id,
                    'issue_date' => $issueDate->toDateString(),
                    'due_date' => $dueDate->toDateString(),
                    'status' => $faker->randomElement(['draft', 'sent']),
                    'notes' => $faker->boolean(35) ? $faker->randomElement($notes) : null,
                    'discount' => $discount,
                    'line_items' => $lineItems,
                ]);

                $roll = $faker->numberBetween(1, 100);
                if ($roll <= 45) {
                    $invoiceService->addPayment($invoice, [
                        'payment_method_id' => $paymentMethods->random()->id,
                        'amount' => (float) $invoice->total,
                        'paid_at' => $faker->dateTimeBetween($issueDate, 'now')->format('Y-m-d'),
                        'notes' => $faker->boolean(30) ? 'Paid in full' : null,
                    ]);
                } elseif ($roll <= 70) {
                    $partial = round((float) $invoice->total * $faker->randomFloat(2, 0.2, 0.7), 2);
                    $invoiceService->addPayment($invoice, [
                        'payment_method_id' => $paymentMethods->random()->id,
                        'amount' => $partial,
                        'paid_at' => $faker->dateTimeBetween($issueDate, 'now')->format('Y-m-d'),
                        'notes' => 'Partial payment',
                    ]);
                }
            }
        });
    }
}
