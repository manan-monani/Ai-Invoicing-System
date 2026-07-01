<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permissions;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\BusinessSetting;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\User;
use App\Services\BusinessSettingService;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(protected BusinessSettingService $businessSettingService) {}

    public function index(): Response
    {
        Gate::authorize(Permissions::DASHBOARD_VIEW);

        $now = now();
        $today = $now->toDateString();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $startOfWeek = $now->copy()->startOfWeek();
        $endOfWeek = $now->copy()->endOfWeek();
        $startOfDay = $now->copy()->startOfDay();
        $endOfDay = $now->copy()->endOfDay();
        $dueSoonEnd = $now->copy()->addDays(7)->endOfDay();

        $revenueMonth = (float) Invoice::query()
            ->whereBetween('issue_date', [$startOfMonth, $endOfMonth])
            ->sum('total');

        $outstanding = (float) Invoice::query()
            ->where('balance', '>', 0)
            ->sum('balance');

        $overdueAmount = (float) Invoice::query()
            ->where('balance', '>', 0)
            ->whereDate('due_date', '<', $today)
            ->sum('balance');

        $paidMonth = (float) InvoicePayment::query()
            ->whereBetween('paid_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $statusSummary = [
            'draft' => Invoice::query()
                ->where('status', 'draft')
                ->where('balance', '>', 0)
                ->whereDate('due_date', '>=', $today)
                ->count(),
            'sent' => Invoice::query()
                ->where('status', 'sent')
                ->where('balance', '>', 0)
                ->whereDate('due_date', '>=', $today)
                ->count(),
            'paid' => Invoice::query()
                ->where('balance', '<=', 0)
                ->count(),
            'overdue' => Invoice::query()
                ->where('balance', '>', 0)
                ->whereDate('due_date', '<', $today)
                ->count(),
        ];

        $paymentsSummary = [
            'today' => (float) InvoicePayment::query()
                ->whereBetween('paid_at', [$startOfDay, $endOfDay])
                ->sum('amount'),
            'week' => (float) InvoicePayment::query()
                ->whereBetween('paid_at', [$startOfWeek, $endOfWeek])
                ->sum('amount'),
            'month' => (float) InvoicePayment::query()
                ->whereBetween('paid_at', [$startOfMonth, $endOfMonth])
                ->sum('amount'),
        ];

        $alerts = [
            'overdue' => $statusSummary['overdue'],
            'due_soon' => Invoice::query()
                ->where('balance', '>', 0)
                ->whereBetween('due_date', [$startOfDay, $dueSoonEnd])
                ->count(),
        ];

        $settings = $this->businessSettingService->getSettings([
            BusinessSetting::TAX_ENABLED,
        ]);
        $taxEnabled = filter_var($settings->get(BusinessSetting::TAX_ENABLED, false), FILTER_VALIDATE_BOOLEAN);
        $taxCollected = $taxEnabled
            ? (float) Invoice::query()
                ->whereBetween('issue_date', [$startOfMonth, $endOfMonth])
                ->sum('tax_total')
            : 0.0;

        return Inertia::render('Admin/Pages/Dashboard', [
            'kpis' => [
                'revenue_month' => $revenueMonth,
                'outstanding' => $outstanding,
                'overdue' => $overdueAmount,
                'paid_month' => $paidMonth,
            ],
            'status_summary' => $statusSummary,
            'payments_summary' => $paymentsSummary,
            'alerts' => $alerts,
            'tax_summary' => [
                'enabled' => $taxEnabled,
                'total' => $taxCollected,
            ],
            'recent_invoices' => Inertia::defer(fn () => InvoiceResource::collection(
                Invoice::query()
                    ->with('customer')
                    ->latest()
                    ->limit(10)
                    ->get()
            )),
            'overdue_invoices' => Inertia::defer(fn () => InvoiceResource::collection(
                Invoice::query()
                    ->with('customer')
                    ->where('balance', '>', 0)
                    ->whereDate('due_date', '<', $today)
                    ->orderBy('due_date')
                    ->limit(10)
                    ->get()
            )),
            'top_customers' => Inertia::defer(function () {
                return User::query()
                    ->select('users.id', 'users.name', 'users.email')
                    ->selectRaw('SUM(invoices.total) as total_billed')
                    ->selectRaw('SUM(invoices.balance) as outstanding')
                    ->join('invoices', 'invoices.customer_id', '=', 'users.id')
                    ->where('users.type', UserType::CLIENT)
                    ->groupBy('users.id', 'users.name', 'users.email')
                    ->orderByDesc('total_billed')
                    ->limit(10)
                    ->get()
                    ->map(fn (User $user) => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'total_billed' => (float) ($user->getAttribute('total_billed') ?? 0),
                        'outstanding' => (float) ($user->getAttribute('outstanding') ?? 0),
                    ]);
            }),
            'sales_trend' => Inertia::defer(function () use ($now) {
                $trendStart = $now->copy()->subMonths(5)->startOfMonth();
                $trendEnd = $now->copy()->endOfMonth();

                $billed = Invoice::query()
                    ->selectRaw('DATE_FORMAT(issue_date, "%Y-%m") as month')
                    ->selectRaw('SUM(total) as total')
                    ->whereBetween('issue_date', [$trendStart, $trendEnd])
                    ->groupBy('month')
                    ->orderBy('month')
                    ->pluck('total', 'month');

                $collected = InvoicePayment::query()
                    ->selectRaw('DATE_FORMAT(paid_at, "%Y-%m") as month')
                    ->selectRaw('SUM(amount) as total')
                    ->whereBetween('paid_at', [$trendStart, $trendEnd])
                    ->groupBy('month')
                    ->orderBy('month')
                    ->pluck('total', 'month');

                $labels = [];
                $billedSeries = [];
                $collectedSeries = [];

                for ($i = 0; $i < 6; $i++) {
                    $month = $trendStart->copy()->addMonths($i);
                    $key = $month->format('Y-m');

                    $labels[] = $month->format('M Y');
                    $billedSeries[] = (float) ($billed[$key] ?? 0);
                    $collectedSeries[] = (float) ($collected[$key] ?? 0);
                }

                return [
                    'labels' => $labels,
                    'billed' => $billedSeries,
                    'collected' => $collectedSeries,
                ];
            }),
        ]);
    }
}
