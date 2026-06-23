<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\BaseController;
use App\Models\Invoice;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class InvoiceController extends BaseController
{
    /**
     * GET /account/invoices
     * Liste les factures de l'utilisateur connecté.
     */
    public function index(): View
    {
        $invoices = Invoice::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(20);

        return $this->renderView('account.invoices.index', compact('invoices'),
            'Mes factures', '');
    }

    /**
     * GET /account/invoices/{invoice}/download
     * Télécharge le PDF d'une facture (vérification de propriété).
     */
    public function download(Invoice $invoice): Response
    {
        // Vérification que la facture appartient à l'utilisateur connecté
        abort_unless($invoice->user_id === auth()->id(), 403);
        abort_unless($invoice->pdf_path && Storage::disk('private')->exists($invoice->pdf_path), 404);

        return response(
            Storage::disk('private')->get($invoice->pdf_path),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $invoice->invoice_number . '.pdf"',
            ]
        );
    }
}
