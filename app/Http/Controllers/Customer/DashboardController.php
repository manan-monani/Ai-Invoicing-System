<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $now = now();
        $today = $now->toDateString();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $startOfDay = $now->copy()->startOfDay();
        $dueSoonEnd = $now->copy()->addDays(7)->endOfDay();

        $outstanding = (float) Invoice::query()
            ->where('customer_id', $user->id)
            ->where('balance', '>', 0)
            ->sum('balance');

        $overdueAmount = (float) Invoice::query()
            ->where('customer_id', $user->id)
            ->where('balance', '>', 0)
            ->whereDate('due_date', '<', $today)
            ->sum('balance');

        $paidMonth = (float) InvoicePayment::query()
            ->whereHas('invoice', fn ($query) => $query->where('customer_id', $user->id))
            ->whereBetween('paid_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $nextDueInvoice = Invoice::query()
            ->where('customer_id', $user->id)
            ->where('balance', '>', 0)
            ->whereDate('due_date', '>=', $today)
            ->orderBy('due_date')
            ->orderBy('id')
            ->first(['id', 'due_date', 'balance']);

        $statusSummary = [
            'draft' => Invoice::query()
                ->where('customer_id', $user->id)
                ->where('status', 'draft')
                ->where('balance', '>', 0)
                ->whereDate('due_date', '>=', $today)
                ->count(),
            'sent' => Invoice::query()
                ->where('customer_id', $user->id)
                ->where('status', 'sent')
                ->where('balance', '>', 0)
                ->whereDate('due_date', '>=', $today)
                ->count(),
            'paid' => Invoice::query()
                ->where('customer_id', $user->id)
                ->where('balance', '<=', 0)
                ->count(),
            'overdue' => Invoice::query()
                ->where('customer_id', $user->id)
                ->where('balance', '>', 0)
                ->whereDate('due_date', '<', $today)
                ->count(),
        ];

        $alerts = [
            'overdue' => $statusSummary['overdue'],
            'due_soon' => Invoice::query()
                ->where('customer_id', $user->id)
                ->where('balance', '>', 0)
                ->whereBetween('due_date', [$startOfDay, $dueSoonEnd])
                ->count(),
        ];

        return Inertia::render('Customer/Pages/Dashboard', [
            'kpis' => [
                'outstanding' => $outstanding,
                'overdue' => $overdueAmount,
                'paid_month' => $paidMonth,
                'next_due' => $nextDueInvoice ? [
                    'id' => $nextDueInvoice->id,
                    'readable_id' => str_pad((string) $nextDueInvoice->id, 6, '0', STR_PAD_LEFT),
                    'due_date' => $nextDueInvoice->due_date,
                    'balance' => (float) $nextDueInvoice->balance,
                ] : null,
            ],
            'status_summary' => $statusSummary,
            'alerts' => $alerts,
            'statement_available' => false,
            'recent_invoices' => Inertia::defer(fn () => InvoiceResource::collection(
                Invoice::query()
                    ->where('customer_id', $user->id)
                    ->orderByDesc('issue_date')
                    ->orderByDesc('id')
                    ->limit(10)
                    ->get()
            )),
            'overdue_invoices' => Inertia::defer(fn () => InvoiceResource::collection(
                Invoice::query()
                    ->where('customer_id', $user->id)
                    ->where('balance', '>', 0)
                    ->whereDate('due_date', '<', $today)
                    ->orderBy('due_date')
                    ->limit(5)
                    ->get()
            )),
            'payment_history' => Inertia::defer(fn () => InvoicePayment::query()
                ->whereHas('invoice', fn ($query) => $query->where('customer_id', $user->id))
                ->with(['invoice:id,customer_id,total', 'paymentMethod:id,name'])
                ->latest('paid_at')
                ->limit(10)
                ->get()
                ->map(fn (InvoicePayment $payment) => [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'paid_at' => $payment->paid_at,
                    'method' => $payment->paymentMethod?->name ?? $payment->notes ?? 'Manual',
                    'invoice' => $payment->invoice ? [
                        'id' => $payment->invoice->id,
                        'readable_id' => str_pad((string) $payment->invoice->id, 6, '0', STR_PAD_LEFT),
                        'total' => $payment->invoice->total,
                    ] : null,
                ])),
        ]);
    }
}
