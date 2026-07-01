<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permissions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PaymentMethodIndexRequest;
use App\Http\Requests\Admin\StorePaymentMethodRequest;
use App\Http\Requests\Admin\UpdatePaymentMethodRequest;
use App\Http\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;
use App\Services\PaymentMethodService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PaymentMethodController extends Controller
{
    public function __construct(protected PaymentMethodService $paymentMethodService) {}

    public function index(PaymentMethodIndexRequest $request): Response
    {
        Gate::authorize(Permissions::PAYMENT_METHODS);

        $data = $request->validated();
        $sort = $data['sort'] ?? 'newest';
        $perPage = (int) ($data['per_page'] ?? 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
        $search = trim((string) ($data['search'] ?? ''));

        $paymentMethods = PaymentMethod::query()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderByDesc('is_default')
            ->when($sort === 'oldest', fn ($query) => $query->orderBy('created_at'))
            ->when($sort === 'newest', fn ($query) => $query->orderByDesc('created_at'))
            ->when($sort === 'name_asc', fn ($query) => $query->orderBy('name'))
            ->when($sort === 'name_desc', fn ($query) => $query->orderByDesc('name'))
            ->when(! in_array($sort, ['oldest', 'newest', 'name_asc', 'name_desc'], true), fn ($query) => $query->orderByDesc('created_at'))
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/System/PaymentMethods', [
            'payment_methods' => PaymentMethodResource::collection($paymentMethods),
            'payment_methods_count' => $paymentMethods->total(),
            'filters' => [
                'sort' => $sort,
                'per_page' => $perPage,
                'search' => $search !== '' ? $search : null,
            ],
        ]);
    }

    public function store(StorePaymentMethodRequest $request): RedirectResponse
    {
        Gate::authorize(Permissions::PAYMENT_METHODS);

        $this->paymentMethodService->create($request->validated());

        return back()->with('success', 'Payment method created successfully.');
    }

    public function update(UpdatePaymentMethodRequest $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        Gate::authorize(Permissions::PAYMENT_METHODS);

        $this->paymentMethodService->update($paymentMethod, $request->validated());

        return back()->with('success', 'Payment method updated successfully.');
    }

    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        Gate::authorize(Permissions::PAYMENT_METHODS);

        $this->paymentMethodService->delete($paymentMethod);

        return back()->with('success', 'Payment method deleted successfully.');
    }
}
