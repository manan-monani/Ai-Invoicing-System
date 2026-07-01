<?php

namespace App\Mail;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;

class InvoiceSent extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Invoice $invoice,
        public string $currencySymbol,
        public string $currencyPosition,
        public string $invoicePrefix,
        public string $invoiceTerms = ''
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $readableId = str_pad((string) $this->invoice->id, 6, '0', STR_PAD_LEFT);
        $label = $this->invoicePrefix !== '' ? $this->invoicePrefix.$readableId : $readableId;

        return new Envelope(
            subject: "Invoice #{$label}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice-sent',
            with: [
                'invoice' => $this->invoice,
                'currencySymbol' => $this->currencySymbol,
                'currencyPosition' => $this->currencyPosition,
                'invoicePrefix' => $this->invoicePrefix,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $readableId = str_pad((string) $this->invoice->id, 6, '0', STR_PAD_LEFT);
        $label = $this->invoicePrefix !== '' ? $this->invoicePrefix.$readableId : $readableId;
        $this->preparePdfEnvironment();

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $this->invoice,
            'currencySymbol' => $this->currencySymbol,
            'currencyPosition' => $this->currencyPosition,
            'invoicePrefix' => $this->invoicePrefix,
            'invoiceTerms' => $this->invoiceTerms,
        ])
            ->setPaper('a4')
            ->setOption('isFontSubsettingEnabled', true)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return [
            Attachment::fromData(fn () => $pdf->output(), "invoice-{$label}.pdf")
                ->withMime('application/pdf'),
        ];
    }

    private function preparePdfEnvironment(): void
    {
        if (function_exists('ini_set')) {
            ini_set('memory_limit', '512M');
        }

        $fontCachePath = storage_path('fonts');

        if (! File::exists($fontCachePath)) {
            File::makeDirectory($fontCachePath, 0755, true);
        }
    }
}
