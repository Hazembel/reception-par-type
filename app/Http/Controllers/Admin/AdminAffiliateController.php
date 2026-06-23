<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Affiliate;
use App\Models\AffiliateEarning;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminAffiliateController extends BaseController
{
    /**
     * GET /admin/affiliates
     * Liste des affiliés + commissions en attente.
     */
    public function index(): View
    {
        // Sécurité : action réservée aux administrateurs (niveau 8).
        abort_unless(auth()->user()?->isAdmin(), 403, 'Accès réservé aux administrateurs.');

        $affiliates = Affiliate::with([
                'user',
                'earnings' => fn ($q) => $q->whereIn('status', ['pending', 'approved']),
            ])
            ->withCount(['clicks', 'earnings'])
            ->orderByDesc('created_at')
            ->paginate(20);

        $pendingTotal = AffiliateEarning::pending()->sum('commission_cts');
        $approvedTotal= AffiliateEarning::approved()->sum('commission_cts');

        return $this->renderView('admin.affiliates.index',
            compact('affiliates', 'pendingTotal', 'approvedTotal'),
            'Affiliés | Admin', ''
        );
    }

    /**
     * POST /admin/affiliates/{affiliate}/approve
     * Valide toutes les commissions "pending" d'un affilié.
     */
    public function approve(Affiliate $affiliate): RedirectResponse
    {
        // Sécurité : action réservée aux administrateurs (niveau 8).
        abort_unless(auth()->user()?->isAdmin(), 403, 'Accès réservé aux administrateurs.');

        AffiliateEarning::where('affiliate_id', $affiliate->id)
            ->pending()
            ->update(['status' => 'approved']);

        return back()->with('success', "Commissions de {$affiliate->affiliate_code} approuvées.");
    }

    /**
     * POST /admin/affiliates/{affiliate}/pay
     * Marque toutes les commissions "approved" comme payées.
     */
    public function pay(Request $request, Affiliate $affiliate): RedirectResponse
    {
        // Sécurité : action réservée aux administrateurs (niveau 8).
        abort_unless(auth()->user()?->isAdmin(), 403, 'Accès réservé aux administrateurs.');

        $validated = $request->validate([
            'payment_reference' => ['required', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($affiliate, $validated) {
            AffiliateEarning::where('affiliate_id', $affiliate->id)
                ->approved()
                ->update([
                    'status'            => 'paid',
                    'payment_reference' => $validated['payment_reference'],
                    'paid_at'           => now(),
                ]);

            $affiliate->update(['last_paid_at' => now()]);
        });

        return back()->with('success', "Paiement enregistré pour {$affiliate->affiliate_code}.");
    }

    /**
     * POST /admin/affiliates/{affiliate}/activate
     * Active ou suspend un compte affilié.
     */
    public function toggleStatus(Affiliate $affiliate): RedirectResponse
    {
        // Sécurité : action réservée aux administrateurs (niveau 8).
        abort_unless(auth()->user()?->isAdmin(), 403, 'Accès réservé aux administrateurs.');

        $newStatus = $affiliate->status === 'active' ? 'suspended' : 'active';
        $affiliate->update(['status' => $newStatus]);
        return back()->with('success', "Statut mis à jour : {$newStatus}.");
    }
}
