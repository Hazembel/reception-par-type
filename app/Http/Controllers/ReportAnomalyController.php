<?php

namespace App\Http\Controllers;

use App\Models\AnomalyReport;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Controller : ReportAnomalyController
 *
 * Traite les signalements d'anomalies soumis via le modal Alpine.js.
 * Protégé par rate limiting (3 signalements / 10 minutes par IP).
 */
class ReportAnomalyController extends BaseController
{
    public function store(Request $request): JsonResponse
    {
        // ── Rate limiting — 3 signalements / 10 minutes par IP ───────────────
        $key = 'anomaly_report:' . ($request->ip() ?? 'unknown');

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => __('anomaly.rate_limit', ['seconds' => $seconds]),
            ], 429);
        }

        RateLimiter::hit($key, 600); // Fenêtre de 10 minutes

        // ── Validation ────────────────────────────────────────────────────────
        $validated = $request->validate([
            'numero_tg'    => ['required', 'string', 'max:25', 'regex:/^[\d\.\-\s\*]{5,25}$/'],
            'vehicle_id'   => ['nullable', 'string', 'max:26'], // ULID
            'field_reported'=> ['nullable', 'string', 'max:100',
                               'in:numero_tg,marque,modele,variante,energie,puissance_kw,cylindree,
                                  boite_vitesse,poids_vide,poids_total,poids_remorquable,co2,
                                  code_emissions,nb_trous,entraxe,alesage,deport_et,pneus_origine,autre'],
            'description'  => ['required', 'string', 'min:10', 'max:1000'],
            'reporter_email'=> ['nullable', 'email', 'max:255'],
        ]);

        // ── Honeypot anti-spam (champ caché) ──────────────────────────────────
        if ($request->filled('website')) { // Champ honeypot nommé "website"
            return response()->json(['success' => true]); // Faux succès pour les bots
        }

        // ── Enregistrement en base ────────────────────────────────────────────
        $report = AnomalyReport::create([
            'numero_tg'         => $validated['numero_tg'],
            'vehicle_id'        => $validated['vehicle_id'] ?? null,
            'field_reported'    => $validated['field_reported'] ?? null,
            'description'       => $validated['description'],
            'reporter_user_id'  => auth()->id(),
            'reporter_email'    => $validated['reporter_email']
                                ?? auth()->user()?->email,
            'status'            => 'pending',
            'ip_address'        => $request->ip(),
        ]);

        // ── Notification e-mail à l'admin ─────────────────────────────────────
        try {
            Mail::to(config('mail.admin_address', 'admin@reception-par-type.ch'))
                ->queue(new \App\Mail\AnomalyReportMail($report));
        } catch (\Throwable $e) {
            // L'e-mail n'est pas critique — le rapport est déjà en BDD
            \Illuminate\Support\Facades\Log::warning('Anomaly mail failed', [
                'report_id' => $report->id,
                'error'     => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => __('anomaly.report_sent'),
            'ref'     => $report->id,
        ]);
    }
}


// ────────────────────────────────────────────────────────────────────────────
// Modèle AnomalyReport
// (Normalement dans app/Models/AnomalyReport.php)
// ────────────────────────────────────────────────────────────────────────────
