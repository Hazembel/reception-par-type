<?php

namespace App\Listeners;

use App\Events\PaymentSucceeded;
use App\Jobs\GenerateSwissInvoice;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Listener : CreateInvoiceOnPayment
 * Dispatche le job de génération de facture après un paiement réussi.
 */
class CreateInvoiceOnPayment implements ShouldQueue
{
    public string $queue = 'invoices';

    public function handle(PaymentSucceeded $event): void
    {
        GenerateSwissInvoice::fromEvent($event)->onQueue('invoices')->dispatch();
    }
}
