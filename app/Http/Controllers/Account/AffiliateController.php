<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\BaseController;
use App\Models\Affiliate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AffiliateController extends BaseController
{
    /**
     * GET /account/affiliate
     * Tableau de bord affilié : clics, gains, statut.
     */
    public function index(): View
    {
        $user      = auth()->user();
        $affiliate = Affiliate::where('user_id', $user->id)->first();

        if (!$affiliate) {
            // Vue d'invitation à rejoindre le programme
            return $this->renderView('account.affiliate.join', [], 'Programme Affilié', '');
        }

        // Statistiques mensuelles
        $thisMonthClicks = $affiliate->clicks()->thisMonth()->count();
        $thisMonthConvs  = $affiliate->clicks()->thisMonth()->where('converted', true)->count();

        // Gains récents (20 derniers)
        $recentEarnings = $affiliate->earnings()
            ->latest()
            ->limit(20)
            ->get();

        // Gains en attente
        $pendingCts  = $affiliate->earnings()->whereIn('status', ['pending', 'approved'])->sum('commission_cts');
        $totalPaidCts= $affiliate->earnings()->paid()->sum('commission_cts');

        // URL d'affiliation
        $refUrl = url('/') . '?ref=' . $affiliate->affiliate_code;

        return $this->renderView('account.affiliate.index', compact(
            'affiliate', 'thisMonthClicks', 'thisMonthConvs',
            'recentEarnings', 'pendingCts', 'totalPaidCts', 'refUrl'
        ), 'Mon espace affilié', '');
    }

    /**
     * POST /account/affiliate/join
     * Demande d'accès au programme affilié.
     */
    public function join(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if (Affiliate::where('user_id', $user->id)->exists()) {
            return redirect()->route('account.affiliate.index', ['locale' => app()->getLocale()]);
        }

        $validated = $request->validate([
            'code'             => ['required', 'string', 'min:3', 'max:30',
                                   'regex:/^[A-Z0-9\-]+$/',
                                   'unique:affiliates,affiliate_code'],
            'beneficiary_name' => ['required', 'string', 'max:150'],
            'iban'             => ['required', 'string', 'regex:/^CH\d{2}[A-Z0-9]{17}$/'],
        ]);

        Affiliate::create([
            'user_id'                 => $user->id,
            'affiliate_code'          => strtoupper($validated['code']),
            'commission_rate_permille'=> 100, // 10% par défaut
            'iban_encrypted'          => $validated['iban'],
            'beneficiary_name'        => $validated['beneficiary_name'],
            'status'                  => 'pending', // Validation admin requise
        ]);

        return redirect()
            ->route('account.affiliate.index', ['locale' => app()->getLocale()])
            ->with('success', 'Votre demande a été soumise. Elle sera examinée sous 48h.');
    }
}


// ══════════════════════════════════════════════════════════════════════════════
// Controller : Admin\AdminAffiliateController
// ══════════════════════════════════════════════════════════════════════════════
