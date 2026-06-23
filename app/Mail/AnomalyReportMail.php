<?php

namespace App\Mail;

use App\Models\AnomalyReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mail : AnomalyReportMail
 *
 * Notifie l'administrateur qu'un visiteur a signalé une anomalie sur une fiche.
 * Envoyé en file d'attente (queue) par ReportAnomalyController.
 */
class AnomalyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly AnomalyReport $report,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Signalement d\'anomalie #' . $this->report->id . ' — reception-par-type.ch',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.anomaly',
            with: [
                'report' => $this->report,
            ],
        );
    }
}
