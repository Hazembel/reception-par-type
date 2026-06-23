<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentSucceeded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string  $orderId,
        public readonly int     $amountCts,
        public readonly ?string $affiliateCode,
        public readonly ?User   $buyer,
        public readonly string  $payerEmail,
        public readonly array   $paypalPayload,
    ) {}
}
