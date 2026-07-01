<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permissions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Invoices\StoreInvoicePaymentRequest;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Gate;

class InvoicePaymentController extends Controller
{
    public function __construct(protected InvoiceService $invoiceService) {}

    public function store(StoreInvoicePaymentRequest $request, Invoice $invoice): \Illuminate\Http\RedirectResponse
    {
        Gate::authorize(Permissions::INVOICES);

        $this->invoiceService->addPayment($invoice, $request->validated());

        return back()->with('success', 'Payment recorded successfully.');
    }
}
