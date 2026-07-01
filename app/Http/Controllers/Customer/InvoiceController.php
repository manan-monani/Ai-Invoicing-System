<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\BusinessSetting;
use App\Models\Invoice;
use App\Services\BusinessSettingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class InvoiceController extends Controller
{
    public function __construct(protected BusinessSettingService $businessSettingService) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $perPage = (int) $request->integer('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $invoices = Invoice::query()
            ->where('customer_id', $user->id)
            ->orderByDesc('issue_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Customer/Invoices/Index', [
            'invoices' => InvoiceResource::collection($invoices),
        ]);
    }

    public function show(Request $request, Invoice $invoice): Response
    {
        $user = $request->user();

        if ($invoice->customer_id !== $user->id) {
            abort(403);
        }

        $invoice->load([
            'customer',
            'lineItems.item',
            'lineItems.tax',
            'payments.paymentMethod',
        ]);

        return Inertia::render('Customer/Invoices/Show', [
            'invoice' => new InvoiceResource($invoice),
        ]);
    }

    public function download(Request $request, Invoice $invoice): SymfonyResponse
    {
        $user = $request->user();

        if ($invoice->customer_id !== $user->id) {
            abort(403);
        }

        $invoice->load(['customer', 'lineItems']);

        $settings = $this->businessSettingService->getSettings([
            'currency_symbol',
            BusinessSetting::CURRENCY_POSITION,
            BusinessSetting::INVOICE_PREFIX,
        ]);

        $currencySymbol = $settings->get('currency_symbol', '$');
        $currencyPosition = $settings->get(BusinessSetting::CURRENCY_POSITION, 'left');
        $invoicePrefix = $settings->get(BusinessSetting::INVOICE_PREFIX, '');
        $readableId = str_pad((string) $invoice->id, 6, '0', STR_PAD_LEFT);
        $label = $invoicePrefix !== '' ? $invoicePrefix.$readableId : $readableId;
        $fontCachePath = storage_path('fonts');

        if (function_exists('ini_set')) {
            ini_set('memory_limit', '512M');
        }

        if (! File::exists($fontCachePath)) {
            File::makeDirectory($fontCachePath, 0755, true);
        }

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'currencySymbol' => $currencySymbol,
            'currencyPosition' => $currencyPosition,
            'invoicePrefix' => $invoicePrefix,
        ])
            ->setPaper('a4')
            ->setOption('isFontSubsettingEnabled', true)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return $pdf->stream("invoice-{$label}.pdf", ['Attachment' => 0]);
    }
}
