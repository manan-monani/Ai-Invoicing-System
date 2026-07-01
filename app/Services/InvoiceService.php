<?php

namespace App\Services;

use App\Models\BusinessSetting;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Item;
use App\Models\PaymentMethod;
use App\Models\Tax;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function __construct(protected BusinessSettingService $businessSettingService) {}

    public function create(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            [$taxEnabled, $taxMode, $defaultTaxId] = $this->resolveTaxSettings();

            $lineItems = $this->buildLineItems($data['line_items'] ?? [], $taxEnabled, $taxMode);
            [$subtotal, $taxTotal] = $this->calculateTotals($lineItems, $taxEnabled, $taxMode, $defaultTaxId);

            $discount = $this->normalizeMoney($data['discount'] ?? 0);
            $total = max($subtotal + $taxTotal - $discount, 0);
            $balance = $total;

            $status = $this->resolveStatus($data['status'] ?? 'draft', $data['due_date'], $balance);

            $invoice = Invoice::create([
                'customer_id' => $data['customer_id'],
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'],
                'status' => $status,
                'notes' => $data['notes'] ?? null,
                'discount' => $discount,
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'total' => $total,
                'balance' => $balance,
            ]);

            $invoice->lineItems()->createMany($lineItems);

            return $invoice->fresh(['customer', 'lineItems', 'payments']);
        });
    }

    public function update(Invoice $invoice, array $data): Invoice
    {
        return DB::transaction(function () use ($invoice, $data) {
            [$taxEnabled, $taxMode, $defaultTaxId] = $this->resolveTaxSettings();

            $lineItems = $this->buildLineItems($data['line_items'] ?? [], $taxEnabled, $taxMode);
            [$subtotal, $taxTotal] = $this->calculateTotals($lineItems, $taxEnabled, $taxMode, $defaultTaxId);

            $discount = $this->normalizeMoney($data['discount'] ?? 0);
            $total = max($subtotal + $taxTotal - $discount, 0);
            $paymentsTotal = (float) $invoice->payments()->sum('amount');
            $balance = max($total - $paymentsTotal, 0);

            $status = $this->resolveStatus($data['status'] ?? 'draft', $data['due_date'], $balance);

            $invoice->update([
                'customer_id' => $data['customer_id'],
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'],
                'status' => $status,
                'notes' => $data['notes'] ?? null,
                'discount' => $discount,
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'total' => $total,
                'balance' => $balance,
            ]);

            $invoice->lineItems()->delete();
            $invoice->lineItems()->createMany($lineItems);

            return $invoice->fresh(['customer', 'lineItems', 'payments']);
        });
    }

    public function delete(Invoice $invoice): void
    {
        $invoice->delete();
    }

    public function addPayment(Invoice $invoice, array $data): InvoicePayment
    {
        return DB::transaction(function () use ($invoice, $data) {
            $paymentMethodId = $data['payment_method_id'] ?? null;
            if (! $paymentMethodId) {
                $paymentMethodId = PaymentMethod::query()
                    ->where('is_default', true)
                    ->value('id');
            }

            $payment = $invoice->payments()->create([
                'payment_method_id' => $paymentMethodId,
                'amount' => $this->normalizeMoney($data['amount']),
                'paid_at' => $data['paid_at'],
                'notes' => $data['notes'] ?? null,
            ]);

            $paymentsTotal = (float) $invoice->payments()->sum('amount');
            $balance = max((float) $invoice->total - $paymentsTotal, 0);

            $status = $this->resolveStatus($invoice->status, $invoice->due_date, $balance);

            $invoice->update([
                'balance' => $balance,
                'status' => $status,
            ]);

            return $payment;
        });
    }

    /**
     * @return array{0: bool, 1: string, 2: int|null}
     */
    private function resolveTaxSettings(): array
    {
        $settings = $this->businessSettingService->getSettings([
            BusinessSetting::TAX_ENABLED,
            BusinessSetting::TAX_MODE,
            BusinessSetting::DEFAULT_TAX_ID,
        ]);

        $taxEnabled = filter_var($settings->get(BusinessSetting::TAX_ENABLED, false), FILTER_VALIDATE_BOOLEAN);
        $taxMode = $settings->get(BusinessSetting::TAX_MODE, 'none') ?: 'none';
        $defaultTaxId = $settings->get(BusinessSetting::DEFAULT_TAX_ID);

        return [$taxEnabled, $taxMode, $defaultTaxId ? (int) $defaultTaxId : null];
    }

    /**
     * @param  array<int, array<string, mixed>>  $lineItems
     * @return array<int, array<string, mixed>>
     */
    private function buildLineItems(array $lineItems, bool $taxEnabled, string $taxMode): array
    {
        $itemIds = collect($lineItems)
            ->pluck('item_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        $items = Item::query()
            ->with(['tax', 'category.tax'])
            ->when($itemIds !== [], fn ($query) => $query->whereIn('id', $itemIds))
            ->get()
            ->keyBy('id');

        return collect($lineItems)->map(function ($line) use ($items, $taxEnabled, $taxMode) {
            $itemId = $line['item_id'] ?? null;
            $itemId = $itemId ? (int) $itemId : null;
            $item = $itemId ? $items->get($itemId) : null;
            $qty = (float) $line['qty'];
            $baseUnitPrice = $this->normalizeMoney($line['unit_price']);
            $discountedUnitPrice = $baseUnitPrice;
            if ($item && $item->has_discount && $item->discount_amount !== null) {
                $discountedUnitPrice = $this->applyItemDiscount($baseUnitPrice, $item);
            }
            $lineTotal = round($qty * $discountedUnitPrice, 2);
            $name = isset($line['name']) && trim((string) $line['name']) !== '' ? (string) $line['name'] : ($item?->name);
            $description = isset($line['description']) && trim((string) $line['description']) !== '' ? (string) $line['description'] : ($item?->description);

            $taxId = null;
            if ($taxEnabled) {
                if ($taxMode === 'item') {
                    $taxId = $item?->tax_id;
                } elseif ($taxMode === 'category') {
                    $taxId = $item?->category?->tax_id;
                }
            }

            return [
                'item_id' => $itemId,
                'name' => $name,
                'description' => $description,
                'qty' => $qty,
                'unit_price' => $baseUnitPrice,
                'line_total' => $lineTotal,
                'tax_id' => $taxId,
            ];
        })->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $lineItems
     * @return array{0: float, 1: float}
     */
    private function calculateTotals(array $lineItems, bool $taxEnabled, string $taxMode, ?int $defaultTaxId): array
    {
        $subtotal = collect($lineItems)->sum('line_total');
        $taxTotal = 0.0;

        if (! $taxEnabled || $taxMode === 'none') {
            return [round($subtotal, 2), $taxTotal];
        }

        if ($taxMode === 'global') {
            if ($defaultTaxId) {
                $tax = Tax::query()->find($defaultTaxId);
                if ($tax) {
                    $taxTotal = $this->calculateTaxForAmount($tax, (float) $subtotal, 1);
                }
            }

            return [round($subtotal, 2), round($taxTotal, 2)];
        }

        $taxIds = collect($lineItems)->pluck('tax_id')->filter()->unique()->values();
        if ($taxIds->isEmpty()) {
            return [round($subtotal, 2), $taxTotal];
        }

        $taxes = Tax::query()->whereIn('id', $taxIds)->get()->keyBy('id');

        foreach ($lineItems as $line) {
            $taxId = $line['tax_id'] ?? null;
            if (! $taxId || ! $taxes->has($taxId)) {
                continue;
            }

            $tax = $taxes->get($taxId);
            $taxTotal += $this->calculateTaxForAmount($tax, (float) $line['line_total'], (float) $line['qty']);
        }

        return [round($subtotal, 2), round($taxTotal, 2)];
    }

    private function applyItemDiscount(float $unitPrice, Item $item): float
    {
        if (! $item->has_discount || $item->discount_amount === null) {
            return $unitPrice;
        }

        $discount = (float) $item->discount_amount;
        if ($discount <= 0) {
            return $unitPrice;
        }

        if ($item->discount_is_percentage) {
            $rate = min(max($discount, 0), 100);

            return round(max($unitPrice * (1 - ($rate / 100)), 0), 2);
        }

        return round(max($unitPrice - $discount, 0), 2);
    }

    private function calculateTaxForAmount(Tax $tax, float $amount, float $qty): float
    {
        if ($tax->type === 'fixed') {
            return round($tax->rate * $qty, 2);
        }

        return round($amount * ($tax->rate / 100), 2);
    }

    private function normalizeMoney(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return round((float) $value, 2);
    }

    private function resolveStatus(string $status, mixed $dueDate, float $balance): string
    {
        if ($balance <= 0) {
            return 'paid';
        }

        $due = $dueDate instanceof Carbon ? $dueDate : Carbon::parse($dueDate);
        if ($due->isPast()) {
            return 'overdue';
        }

        return $status === 'sent' ? 'sent' : 'draft';
    }
}
