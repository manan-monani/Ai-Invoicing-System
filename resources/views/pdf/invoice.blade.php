@php
    $readableId = str_pad((string) $invoice->id, 6, '0', STR_PAD_LEFT);
    $invoiceNumber = $invoicePrefix !== '' ? $invoicePrefix.$readableId : $readableId;
    $currencyPosition = $currencyPosition ?? business_config('currency_position', 'left');
    $formatAmount = fn ($value) => $currencyPosition === 'right'
        ? number_format((float) $value, 2).$currencySymbol
        : $currencySymbol.number_format((float) $value, 2);
    $businessName = business_config('business_name', config('app.name', 'Business'));
    $businessTagline = business_config('tagline');
    $contactEmail = business_config('contact_email');
    $contactPhone = business_config('contact_phone');
    $contactAddress = business_config('contact_address');
    $invoiceTerms = $invoiceTerms ?? business_config('invoice_terms');
    $logoValue = business_config('logo_url');
    $logoSrc = null;
    $showAvatar = false;
    $initials = '';
    $primaryColor = business_config('primary', '#4f7fa6');

    if ($logoValue) {
        if (str_starts_with($logoValue, 'http')) {
            // Check if it's dicebear and try to use PNG for better compatibility
            $effectiveUrl = $logoValue;
            if (str_contains($effectiveUrl, 'dicebear.com') && str_contains($effectiveUrl, '/svg')) {
                $effectiveUrl = str_replace('/svg', '/png', $effectiveUrl);
            }

            try {
                $ctx = stream_context_create(['http' => ['timeout' => 5]]);
                $content = @file_get_contents($effectiveUrl, false, $ctx);
                if ($content) {
                    $mime = 'image/png';
                    if (str_contains($effectiveUrl, '.jpg') || str_contains($effectiveUrl, '.jpeg')) $mime = 'image/jpeg';
                    if (str_contains($effectiveUrl, '.gif')) $mime = 'image/gif';
                    $logoSrc = 'data:'.$mime.';base64,'.base64_encode($content);
                }
            } catch (\Exception $e) {
                // Keep logoSrc null
            }
        } else {
            $logoPath = public_path(str_starts_with($logoValue, '/storage/') ? ltrim($logoValue, '/') : 'storage/'.$logoValue);
            if (is_file($logoPath)) {
                $extension = pathinfo($logoPath, PATHINFO_EXTENSION) ?: 'png';
                $logoSrc = 'data:image/'.$extension.';base64,'.base64_encode(file_get_contents($logoPath));
            }
        }

        // If logo was intended but failed to load, prepare fallback avatar
        if (!$logoSrc) {
            $showAvatar = true;
            $nameParts = explode(' ', trim($businessName));
            if (count($nameParts) > 1) {
                $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
            } else {
                $initials = strtoupper(substr($nameParts[0], 0, min(2, strlen($nameParts[0] ?? ''))));
            }
        }
    }

@endphp
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Invoice #{{ $invoiceNumber }}</title>
        <style>
            @font-face {
                font-family: 'ArialUnicode';
                src: url('{{ public_path('fonts/ArialUnicode.ttf') }}') format('truetype');
                font-weight: normal;
                font-style: normal;
            }
            @font-face {
                font-family: 'ArialUnicode';
                src: url('{{ public_path('fonts/ArialUnicode.ttf') }}') format('truetype');
                font-weight: 600;
                font-style: normal;
            }
            @font-face {
                font-family: 'ArialUnicode';
                src: url('{{ public_path('fonts/ArialUnicode.ttf') }}') format('truetype');
                font-weight: bold;
                font-style: normal;
            }
            * { box-sizing: border-box; }
            body { font-family: "ArialUnicode", "DejaVu Sans", Arial, Helvetica, sans-serif; color: #0f172a; margin: 0; padding: 24px; background: #ffffff; }
            .sheet { max-width: 760px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 24px; padding: 24px; }
            .header { display: flex; align-items: flex-start; gap: 16px; }
            .logo { height: 56px; width: 56px; border-radius: 16px; border: 1px solid #f1f5f9; background: #ffffff; display: flex; align-items: center; justify-content: center; overflow: hidden; }
            .logo img { height: 100%; width: 100%; object-fit: cover; display: block; }
            .initials-avatar { background: {{ $primaryColor }}; color: #ffffff; font-size: 22px; font-weight: bold; line-height: 56px; text-align: center; }
            .brand h1 { margin: 0; font-size: 20px; }
            .brand p { margin: 4px 0 0; font-size: 12px; color: #64748b; }
            .meta-line { margin-top: 8px; font-size: 12px; color: #475569; display: flex; gap: 12px; flex-wrap: wrap; }
            .section { margin-top: 20px; }
            .bill-to { border: 1px solid #f1f5f9; border-radius: 16px; padding: 16px; }
            .bill-to h3 { margin: 0 0 10px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; }
            .bill-to strong { font-size: 14px; color: #0f172a; }
            .bill-to .details { display: flex; justify-content: space-between; gap: 16px; font-size: 12px; color: #475569; }
            table { width: 100%; border-collapse: collapse; margin-top: 16px; }
            th, td { padding: 10px 8px; font-size: 12px; }
            th { text-transform: uppercase; letter-spacing: 0.08em; font-size: 10px; color: #94a3b8; border-bottom: 1px solid #e2e8f0; }
            td { border-bottom: 1px solid #f1f5f9; vertical-align: top; }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .totals { margin-top: 16px; display: flex; justify-content: flex-end; }
            .totals table { width: 260px; }
            .totals td { border: none; padding: 6px 0; }
            .totals .label { color: #64748b; }
            .totals .value { text-align: right; font-weight: 400; }
            .totals .value strong { font-weight: 700; }
            .terms { margin-top: 16px; font-size: 11px; color: #475569; }
            .terms .title { font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; margin-bottom: 6px; font-weight: 600; }
            .terms p { margin: 0 0 6px; }
            .terms ul, .terms ol { margin: 0 0 6px 18px; padding: 0; }
            .terms li { margin-bottom: 4px; }
            .signature { margin-top: 24px; border-top: 1px solid #f1f5f9; padding-top: 16px; display: flex; justify-content: flex-end; }
            .signature .line { height: 1px; width: 180px; background: #cbd5f5; margin-bottom: 6px; }
            .signature .label { font-size: 12px; font-weight: 600; color: #475569; text-align: right; }
            .footer { margin-top: 16px; border-top: 1px solid #f1f5f9; padding-top: 12px; font-size: 11px; color: #94a3b8; display: flex; gap: 12px; flex-wrap: wrap; }
        </style>
    </head>
    <body>
        <div class="sheet">
            <div class="header">
                @if($logoSrc)
                    <div class="logo">
                        <img src="{{ $logoSrc }}" alt="Logo" />
                    </div>
                @elseif($showAvatar)
                    <div class="logo initials-avatar">
                        {{ $initials }}
                    </div>
                @endif
                <div class="brand">
                    <h1>{{ $businessName }}</h1>
                    @if($businessTagline)
                        <p>{{ $businessTagline }}</p>
                    @endif
                    <div class="meta-line">
                        <span>Invoice No: {{ $invoiceNumber }}</span>
                        <span>Issue Date: {{ optional($invoice->issue_date)->format('M d, Y') ?? '-' }}</span>
                        <span>Due Date: {{ optional($invoice->due_date)->format('M d, Y') ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="section bill-to">
                <h3>Bill To</h3>
                <div class="details">
                    <div>
                        <strong>{{ $invoice->customer?->name ?? 'Customer' }}</strong><br>
                        {{ $invoice->customer?->email ?? '' }}
                    </div>
                    <div>
                        <div>Invoice Date: {{ optional($invoice->issue_date)->format('M d, Y') ?? '-' }}</div>
                        <div>Due Date: {{ optional($invoice->due_date)->format('M d, Y') ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="section" style="border: 1px solid #f1f5f9; border-radius: 16px; overflow: hidden;">
                <table>
                    <thead style="background: #f1f5f9;">
                        <tr>
                            <th style="width: 48px;">No</th>
                            <th style="text-align: left;">Description</th>
                            <th class="text-center" style="width: 80px;">Qty</th>
                            <th class="text-center" style="width: 120px;">Price</th>
                            <th class="text-center" style="width: 120px;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoice->lineItems as $index => $line)
                            <tr>
                                <td class="text-center"><strong>{{ $index + 1 }}</strong></td>
                                <td>
                                    <div style="font-weight: 600; color: #0f172a;">{{ $line->name }}</div>
                                    @if($line->description)
                                        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">{{ $line->description }}</div>
                                    @endif
                                </td>
                                <td class="text-center">{{ $line->qty }}</td>
                                <td class="text-center">{{ $formatAmount($line->unit_price) }}</td>
                                <td class="text-center"><strong>{{ $formatAmount($line->line_total) }}</strong></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: #64748b;">No line items.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="totals">
                <table>
                    <tr>
                        <td class="label">Sub Total</td>
                        <td class="value">{{ $formatAmount($invoice->subtotal) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tax</td>
                        <td class="value">{{ $formatAmount($invoice->tax_total) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Discount</td>
                        <td class="value">-{{ $formatAmount($invoice->discount) }}</td>
                    </tr>
                    <tr>
                        <td class="label"><strong>Grand Total</strong></td>
                        <td class="value"><strong>{{ $formatAmount($invoice->total) }}</strong></td>
                    </tr>
                </table>
            </div>

            @if($invoiceTerms)
                <div class="terms">
                    <div class="title">Terms &amp; Conditions</div>
                    {!! $invoiceTerms !!}
                </div>
            @endif

            <div class="signature">
                <div>
                    <div class="line"></div>
                    <div class="label">Signature</div>
                </div>
            </div>

            <div class="footer">
                @if($contactAddress)
                    <span>{{ $contactAddress }}</span>
                @endif
                @if($contactPhone)
                    <span>{{ $contactPhone }}</span>
                @endif
                @if($contactEmail)
                    <span>{{ $contactEmail }}</span>
                @endif
            </div>
        </div>
    </body>
</html>
