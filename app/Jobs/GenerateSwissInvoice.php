<?php

namespace App\Jobs;

use App\Events\PaymentSucceeded;
use App\Mail\InvoiceMail;
use App\Models\Invoice;
use App\Models\PricingPlan;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

/**
 * Job : GenerateSwissInvoice
 *
 * Génère une facture PDF conforme aux normes comptables suisses
 * et l'envoie par e-mail à l'acheteur.
 *
 * Standards suisses respectés :
 *  - Numéro séquentiel unique (RPT-YYYY-NNNNN)
 *  - Coordonnées du vendeur complètes (raison sociale, adresse, IDE/UID)
 *  - Adresse de l'acheteur
 *  - Description détaillée des prestations
 *  - TVA : taux 8.1% (taux normal CH 2024) OU mention d'exonération
 *  - Montant HT + TVA + TTC en CHF
 *  - Conditions de paiement
 *  - Date d'émission et référence PayPal
 */
class GenerateSwissInvoice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;
    public int $tries   = 3;

    public function __construct(
        private readonly string  $orderId,
        private readonly int     $amountCts,
        private readonly ?string $buyerUserId,
        private readonly string  $payerEmail,
        private readonly array   $paypalPayload,
    ) {}

    /**
     * Factory : crée et dispatche le job depuis l'événement PaymentSucceeded.
     */
    public static function fromEvent(PaymentSucceeded $event): static
    {
        return new static(
            orderId:       $event->orderId,
            amountCts:     $event->amountCts,
            buyerUserId:   $event->buyer?->id,
            payerEmail:    $event->payerEmail,
            paypalPayload: $event->paypalPayload,
        );
    }

    public function handle(): void
    {
        // Idempotence (1re barrière) : si la facture existe déjà, on s'arrête.
        if (Invoice::where('paypal_order_id', $this->orderId)->exists()) {
            return;
        }

        $buyer = $this->buyerUserId ? User::find($this->buyerUserId) : null;

        // Idempotence (2e barrière, atomique) : la contrainte UNIQUE sur
        // invoices.paypal_order_id empêche tout doublon même si deux jobs
        // concurrents passaient la 1re barrière en même temps. Dans ce cas,
        // l'INSERT en double lève une QueryException : on l'intercepte et on
        // considère que la facture a déjà été générée par l'autre exécution.
        try {
            $invoice = $this->createInvoiceRecord($buyer);
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::info(
                "Facture déjà générée pour l'ordre {$this->orderId} (course détectée) — skip."
            );
            return;
        }

        // Génération du PDF
        $pdfPath = $this->generatePdf($invoice);
        $invoice->update(['pdf_path' => $pdfPath, 'status' => 'sent']);

        // Envoi par e-mail
        $recipientEmail = $buyer?->email ?? $this->payerEmail;
        if ($recipientEmail) {
            Mail::to($recipientEmail)->queue(new InvoiceMail($invoice, $pdfPath));
        }
    }

    // ── Création de l'enregistrement en BDD ───────────────────────────────────

    private function createInvoiceRecord(?User $buyer): Invoice
    {
        $invoiceNumber = Invoice::generateNumber();

        // Calcul TVA selon la situation de l'entreprise
        // En Suisse : exonéré si CA < 100 000 CHF/an (art. 10 LTVA)
        $isVatExempt   = config('billing.vat_exempt', true);
        $vatRateBp     = $isVatExempt ? 0 : 810;  // 810 = 8.10% en points de base
        $subtotalCts   = $isVatExempt
            ? $this->amountCts
            : (int) round($this->amountCts / (1 + $vatRateBp / 10000));
        $taxAmountCts  = $this->amountCts - $subtotalCts;

        // Adresse de facturation depuis le profil PayPal ou compte utilisateur
        $billingAddress = $this->extractBillingAddress($buyer);

        // Lignes de facturation
        $lineItems = $this->buildLineItems();

        return Invoice::create([
            'user_id'         => $buyer?->id,
            'invoice_number'  => $invoiceNumber,
            'paypal_order_id' => $this->orderId,
            'subtotal_cts'    => $subtotalCts,
            'tax_rate_bp'     => $vatRateBp,
            'tax_amount_cts'  => $taxAmountCts,
            'total_cts'       => $this->amountCts,
            'is_vat_exempt'   => $isVatExempt,
            'currency'        => 'CHF',
            'billing_address' => $billingAddress,
            'line_items'      => $lineItems,
            'status'          => 'draft',
            'issued_at'       => now(),
            'due_at'          => now()->addDays(30),
        ]);
    }

    // ── Génération du PDF (HTML→PDF via Browsershot ou dompdf) ───────────────

    private function generatePdf(Invoice $invoice): string
    {
        $html = view('invoices.pdf', ['invoice' => $invoice])->render();

        $filename = "invoices/{$invoice->invoice_number}.pdf";
        $tempPath = sys_get_temp_dir() . "/{$invoice->invoice_number}.pdf";

        // Utilisation de dompdf (léger, pas de dépendance Chrome)
        $dompdf = new \Dompdf\Dompdf([
            'default_paper_size' => 'A4',
            'enable_remote'      => false, // Sécurité : pas de ressources externes
            'enable_php'         => false, // Sécurité : pas d'exécution PHP
            'chroot'             => storage_path('app/invoices'),
        ]);

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        file_put_contents($tempPath, $dompdf->output());

        // Stockage sur le disque privé (non accessible publiquement)
        Storage::disk('private')->put($filename, file_get_contents($tempPath));
        unlink($tempPath);

        return $filename;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function extractBillingAddress(?User $buyer): array
    {
        $payer = $this->paypalPayload['payer'] ?? [];

        return [
            'name'    => ($payer['name']['given_name'] ?? '') . ' ' . ($payer['name']['surname'] ?? '')
                ?: $buyer?->name ?? 'Client',
            'email'   => $payer['email_address'] ?? $buyer?->email ?? $this->payerEmail,
            'line1'   => $payer['address']['address_line_1'] ?? '',
            'line2'   => $payer['address']['address_line_2'] ?? '',
            'postal'  => $payer['address']['postal_code'] ?? '',
            'city'    => $payer['address']['admin_area_2'] ?? '',
            'country' => $payer['address']['country_code'] ?? 'CH',
        ];
    }

    private function buildLineItems(): array
    {
        $items = $this->paypalPayload['purchase_units'][0]['items'] ?? [];

        if (empty($items)) {
            return [[
                'description' => 'Service reception-par-type.ch',
                'qty'         => 1,
                'unit_cts'    => $this->amountCts,
                'total_cts'   => $this->amountCts,
            ]];
        }

        return array_map(function ($item) {
            $qty     = max(1, (int) ($item['quantity'] ?? 1));
            // Prix unitaire arrondi UNE seule fois, puis total = unit × qty.
            // Évite que total_cts diverge de unit_cts × qty à cause d'un double arrondi.
            $unitCts = (int) round((float) ($item['unit_amount']['value'] ?? 0) * 100);

            return [
                'description' => $item['name'] ?? 'Service',
                'qty'         => $qty,
                'unit_cts'    => $unitCts,
                'total_cts'   => $unitCts * $qty,
            ];
        }, $items);
    }
}


// ══════════════════════════════════════════════════════════════════════════════
// Listener : CreateInvoiceOnPayment
// ══════════════════════════════════════════════════════════════════════════════
