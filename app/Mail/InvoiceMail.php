<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Invoice $invoice,
        public readonly string  $pdfPath,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Votre facture {$this->invoice->invoice_number} — reception-par-type.ch",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.invoice',
            with: [
                'invoice'     => $this->invoice,
                'companyName' => config('billing.company_name'),
                'supportEmail'=> config('billing.email'),
            ],
        );
    }

    public function attachments(): array
    {
        if (!Storage::disk('private')->exists($this->pdfPath)) {
            return [];
        }

        return [
            Attachment::fromStorageDisk('private', $this->pdfPath)
                ->as("{$this->invoice->invoice_number}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}


// ══════════════════════════════════════════════════════════════════════════════
// Controller : Account\InvoiceController
// ══════════════════════════════════════════════════════════════════════════════
