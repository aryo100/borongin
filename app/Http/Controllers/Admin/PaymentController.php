<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\OrderLifecycle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    public function proof(Payment $payment): StreamedResponse
    {
        $disk = Storage::disk('local');
        abort_unless(preg_match('#^payment-proofs/[\da-f-]{36}\.jpg$#D', $payment->path) === 1 && $disk->exists($payment->path), 404);

        return $disk->response($payment->path, 'bukti-pembayaran.jpg', [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function update(Request $request, Payment $payment, OrderLifecycle $lifecycle): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['approve', 'reject'])],
            'note' => ['nullable', 'string', 'max:1000', 'required_if:action,reject'],
        ]);

        if ($data['action'] === 'approve') {
            $lifecycle->approvePayment($payment, $request->user(), $data['note'] ?? null);
        } else {
            $lifecycle->rejectPayment($payment, $request->user(), $data['note']);
        }

        return to_route('admin.orders.show', $payment->order)->with('status', 'Verifikasi pembayaran disimpan.');
    }
}
