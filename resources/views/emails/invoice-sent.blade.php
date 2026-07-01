@php
    $readableId = str_pad((string) $invoice->id, 6, '0', STR_PAD_LEFT);
    $invoiceNumber = $invoicePrefix !== '' ? $invoicePrefix.$readableId : $readableId;
    $currencyPosition = $currencyPosition ?? business_config('currency_position', 'left');
    $formatAmount = fn ($value) => $currencyPosition === 'right'
        ? number_format((float) $value, 2).$currencySymbol
        : $currencySymbol.number_format((float) $value, 2);
@endphp
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Invoice #{{ $invoiceNumber }}</title>
    </head>
    <body style="font-family: Arial, Helvetica, sans-serif; color: #0f172a; margin: 0; padding: 24px; background: #f8fafc;">
        <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px;">
            <h1 style="margin: 0 0 12px; font-size: 20px;">Invoice #{{ $invoiceNumber }}</h1>
            <p style="margin: 0 0 8px; font-size: 14px; color: #475569;">Hello {{ $invoice->customer?->name ?? 'Customer' }},</p>
            <p style="margin: 0 0 16px; font-size: 14px; color: #475569;">
                Please find your invoice attached. If you have any questions, feel free to reach out.
            </p>

            <div style="display: flex; flex-wrap: wrap; gap: 16px; margin-top: 16px;">
                <div style="min-width: 160px;">
                    <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8; margin-bottom: 4px;">Invoice Total</div>
                    <div style="font-size: 14px; font-weight: 600; color: #0f172a;">{{ $formatAmount($invoice->total) }}</div>
                </div>
                <div style="min-width: 160px;">
                    <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8; margin-bottom: 4px;">Due Date</div>
                    <div style="font-size: 14px; font-weight: 600; color: #0f172a;">
                        {{ optional($invoice->due_date)->format('M d, Y') ?? '-' }}
                    </div>
                </div>
            </div>

            <p style="margin: 20px 0 0; font-size: 13px; color: #64748b;">
                Thank you for your business.
            </p>
        </div>
    </body>
</html>
