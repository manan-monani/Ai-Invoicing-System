<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permissions;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Invoices\StoreInvoiceRequest;
use App\Http\Requests\Admin\Invoices\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\ItemResource;
use App\Http\Resources\PaymentMethodResource;
use App\Http\Resources\TaxResource;
use App\Mail\CustomerInvoiceAccessMail;
use App\Mail\InvoiceSent;
use App\Models\BusinessSetting;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\PaymentMethod;
use App\Models\Tax;
use App\Models\User;
use App\Services\BusinessSettingService;
use App\Services\InvoiceService;
use App\Services\UserService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService,
        protected BusinessSettingService $businessSettingService,
        protected UserService $userService
    ) {}

    public function index(): Response
    {
        Gate::authorize(Permissions::INVOICES);

        $sort = request('sort', 'newest');
        $search = trim((string) request('search', ''));
        $perPage = (int) request('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $invoices = Invoice::query()
            ->with('customer')
            ->when($search !== '', function ($query) use ($search) {
                $normalized = ltrim($search, '#');
                $normalized = preg_replace('/\D+/', '', $normalized ?? '');

                if ($normalized === '') {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->where('id', (int) $normalized);
            })
            ->when($sort === 'oldest', fn ($query) => $query->orderBy('created_at'))
            ->when($sort === 'newest', fn ($query) => $query->orderByDesc('created_at'))
            ->when($sort === 'due_asc', fn ($query) => $query->orderBy('due_date'))
            ->when($sort === 'due_desc', fn ($query) => $query->orderByDesc('due_date'))
            ->when(! in_array($sort, ['oldest', 'newest', 'due_asc', 'due_desc'], true), fn ($query) => $query->orderByDesc('created_at'))
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Invoices/Index', [
            'invoices' => InvoiceResource::collection($invoices),
            'filters' => [
                'sort' => $sort,
                'per_page' => $perPage,
                'search' => $search,
            ],
        ]);
    }

    public function create(): Response
    {
        Gate::authorize(Permissions::INVOICES);

        return Inertia::render('Admin/Invoices/Create', [
            'customers' => $this->customerOptions(),
            'items' => ItemResource::collection($this->itemOptions()),
            'taxes' => TaxResource::collection($this->taxOptions()),
            'tax_settings' => $this->taxSettings(),
        ]);
    }

    public function store(StoreInvoiceRequest $request): \Illuminate\Http\RedirectResponse
    {
        Gate::authorize(Permissions::INVOICES);

        $data = $request->validated();
        $customer = $this->resolveCustomer($data);
        $data['customer_id'] = $customer->id;
        unset($data['customer_email'], $data['customer_name']);

        $invoice = $this->invoiceService->create($data);

        return to_route($this->invoiceRouteName($request, 'show'), $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice): Response
    {
        Gate::authorize(Permissions::INVOICES);

        $invoice->load([
            'customer',
            'lineItems.item',
            'lineItems.tax',
            'payments.paymentMethod',
        ]);

        $paymentMethods = PaymentMethod::query()
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
        $defaultPaymentMethodId = $paymentMethods->firstWhere('is_default', true)?->id;

        return Inertia::render('Admin/Invoices/Show', [
            'invoice' => new InvoiceResource($invoice),
            'tax_settings' => $this->taxSettings(),
            'payment_methods' => PaymentMethodResource::collection($paymentMethods),
            'default_payment_method_id' => $defaultPaymentMethodId,
        ]);
    }

    public function edit(Invoice $invoice): Response
    {
        Gate::authorize(Permissions::INVOICES);

        $invoice->load(['customer', 'lineItems.item', 'lineItems.tax', 'payments']);
        $itemIds = $invoice->lineItems
            ->pluck('item_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        return Inertia::render('Admin/Invoices/Edit', [
            'invoice' => new InvoiceResource($invoice),
            'customers' => $this->customerOptions([$invoice->customer_id]),
            'items' => ItemResource::collection($this->itemOptions($itemIds)),
            'taxes' => TaxResource::collection($this->taxOptions()),
            'tax_settings' => $this->taxSettings(),
        ]);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): \Illuminate\Http\RedirectResponse
    {
        Gate::authorize(Permissions::INVOICES);

        $this->invoiceService->update($invoice, $request->validated());

        return to_route($this->invoiceRouteName($request, 'show'), $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Invoice $invoice): \Illuminate\Http\RedirectResponse
    {
        Gate::authorize(Permissions::INVOICES);

        $this->invoiceService->delete($invoice);

        return to_route($this->invoiceRouteName(request(), 'index'))
            ->with('success', 'Invoice deleted successfully.');
    }

    public function sendEmail(\Illuminate\Http\Request $request, Invoice $invoice): \Illuminate\Http\RedirectResponse
    {
        Gate::authorize(Permissions::INVOICES);

        if (! $request->user()?->isAdmin()) {
            abort(403);
        }

        $invoice->load(['customer', 'lineItems']);
        $customer = $invoice->customer;

        if (! $customer || ! $customer->email) {
            return back()->with('error', 'Customer email is not available for this invoice.');
        }

        $settings = $this->businessSettingService->getSettings([
            'currency_symbol',
            BusinessSetting::CURRENCY_POSITION,
            BusinessSetting::INVOICE_PREFIX,
            BusinessSetting::INVOICE_TERMS,
        ]);

        $currencySymbol = $settings->get('currency_symbol', '$');
        $currencyPosition = $settings->get(BusinessSetting::CURRENCY_POSITION, 'left');
        $invoicePrefix = $settings->get(BusinessSetting::INVOICE_PREFIX, '');
        $invoiceTerms = $settings->get(BusinessSetting::INVOICE_TERMS, '');

        try {
            Mail::to($customer->email)->send(new InvoiceSent($invoice, $currencySymbol, $currencyPosition, $invoicePrefix, $invoiceTerms));
        } catch (\Throwable $exception) {
            report($exception);

            $message = 'Failed to send invoice email. Please try again.';
            if (str_contains($exception->getMessage(), 'Allowed memory size')) {
                $message = 'Invoice PDF is too large to generate. Please try again or reduce the invoice size.';
            }

            return back()->with('error', $message);
        }

        return back()->with('success', 'Invoice email sent successfully.');
    }

    private function invoiceRouteName(\Illuminate\Http\Request $request, string $action): string
    {
        $user = $request->user();
        $prefix = $user?->isEmployee() ? 'employee.invoices.' : 'admin.invoices.';

        return $prefix.$action;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function customerOptions(array $ids = []): array
    {
        if ($ids === []) {
            return [];
        }

        return User::query()
            ->where('type', UserType::CLIENT)
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ])
            ->all();
    }

    /**
     * @return \Illuminate\Support\Collection<int, Item>
     */
    private function itemOptions(array $ids = [])
    {
        if ($ids === []) {
            return collect();
        }

        return Item::query()
            ->with(['tax', 'category.tax'])
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return \Illuminate\Support\Collection<int, Tax>
     */
    private function taxOptions()
    {
        return Tax::query()
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function taxSettings(): array
    {
        $settings = $this->businessSettingService->getSettings([
            BusinessSetting::TAX_ENABLED,
            BusinessSetting::TAX_MODE,
            BusinessSetting::DEFAULT_TAX_ID,
        ]);

        $taxEnabled = filter_var($settings->get(BusinessSetting::TAX_ENABLED, false), FILTER_VALIDATE_BOOLEAN);
        $taxMode = $settings->get(BusinessSetting::TAX_MODE, 'none') ?: 'none';
        $defaultTaxId = $settings->get(BusinessSetting::DEFAULT_TAX_ID);

        return [
            'tax_enabled' => $taxEnabled,
            'tax_mode' => $taxEnabled ? $taxMode : 'none',
            'default_tax_id' => $taxEnabled ? ($defaultTaxId ? (int) $defaultTaxId : null) : null,
        ];
    }

    private function resolveCustomer(array $data): User
    {
        if (! empty($data['customer_id'])) {
            return User::query()->findOrFail($data['customer_id']);
        }

        $email = $data['customer_email'] ?? null;
        $name = $data['customer_name'] ?? null;

        if (! $email) {
            abort(422, 'Customer email is required.');
        }

        $existing = User::query()->where('email', $email)->first();
        if ($existing) {
            return $existing;
        }

        $password = Str::password(12);
        $user = $this->userService->create([
            'name' => $name ?? $email,
            'email' => $email,
            'password' => $password,
            'type' => UserType::CLIENT,
        ]);

        Mail::to($user->email)->send(new CustomerInvoiceAccessMail($user, $password));

        return $user;
    }
}
