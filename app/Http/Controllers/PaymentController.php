<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * GET /{locale}/payment/success
     * PayPal redirects here after a successful payment approval.
     * Query params: ?token={ORDER_ID}&PayerID={PAYER_ID}
     * Actual subscription activation is async (webhook PAYMENT.CAPTURE.COMPLETED).
     */
    public function success(Request $request): View
    {
        $orderId = $request->query('token');

        return view('pages.payment.success', [
            'order_id'         => $orderId,
            'meta_title'       => __('payment.success_title') . ' — ' . config('app.name'),
            'meta_description' => '',
            'meta_robots'      => 'noindex, nofollow',
        ]);
    }

    /**
     * GET /{locale}/payment/cancel
     * PayPal redirects here when the user cancels the payment.
     */
    public function cancel(): View
    {
        return view('pages.payment.cancel', [
            'meta_title'       => __('payment.cancel_title') . ' — ' . config('app.name'),
            'meta_description' => '',
            'meta_robots'      => 'noindex, nofollow',
        ]);
    }
}
