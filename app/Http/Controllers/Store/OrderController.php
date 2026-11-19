<?php

namespace App\Http\Controllers\Store;

use App\Models\Order;
use App\Models\Setting;
use App\Services\GuestOrderAccess;
use App\Services\OrderLifecycle;
use App\Services\PaymentProof;
use Dompdf\Dompdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class OrderController
{
    public function index(Request $request): Response
    {
        $orders = Order::query()->where('user_id', $request->user()->id)->latest('id')->paginate(15);

        return response()->view('store.orders.index', ['orders' => $orders], headers: ['Cache-Control' => 'private, no-store']);
    }

    public function show(Order $order): Response
    {
        Gate::authorize('view', $order);

        return response()->view('store.orders.show', [
            'order' => $order->load(['items', 'payments' => fn ($payments) => $payments->latest('id'), 'tickets' => fn ($tickets) => $tickets->latest('id')]),
            'settings' => Setting::query()->pluck('value', 'key'),
        ], headers: ['Cache-Control' => 'private, no-store', 'Referrer-Policy' => 'no-referrer']);
    }

    public function lookup(): View
    {
        return view('store.orders.lookup');
    }

    public function sendLookup(Request $request, GuestOrderAccess $access): RedirectResponse
    {
        $data = $request->validate(['public_id' => ['required', 'ulid'], 'email' => ['required', 'email:rfc', 'max:254']]);
        $order = Order::query()->where('public_id', strtoupper($data['public_id']))->whereNull('user_id')->first();

        if ($order && hash_equals(mb_strtolower($order->email), mb_strtolower($data['email']))) {
            $access->send($order);
        }

        return redirect()->route('store.orders.lookup')->with('status', 'Jika nomor pesanan dan email cocok, tautan akses akan dikirim ke email tersebut.');
    }

    public function access(Request $request, Order $order, GuestOrderAccess $access): RedirectResponse
    {
        abort_unless($order->user_id === null, 403);
        $request->session()->regenerate(true);
        $access->grant($request, $order);

        return redirect()->route('store.orders.show', ['order' => $order->public_id])
            ->withHeaders(['Cache-Control' => 'private, no-store', 'Referrer-Policy' => 'no-referrer']);
    }

    public function resend(Order $order, GuestOrderAccess $access): RedirectResponse
    {
        Gate::authorize('view', $order);
        abort_unless($order->user_id === null, 403);
        $access->send($order);

        return back()->with('status', 'Tautan akses baru akan dikirim ke email pesanan.');
    }

    public function invoice(Order $order): Response
    {
        Gate::authorize('view', $order);
        $pdf = new Dompdf(['isRemoteEnabled' => false, 'isPhpEnabled' => false, 'isJavascriptEnabled' => false]);
        $pdf->setPaper('A4');
        $pdf->loadHtml(view('pdf.order', ['order' => $order->load('items')])->render(), 'UTF-8');
        $pdf->render();

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="invoice-'.$order->public_id.'.pdf"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function payment(Request $request, Order $order, PaymentProof $proof, OrderLifecycle $lifecycle): RedirectResponse
    {
        Gate::authorize('view', $order);
        $data = $request->validate(['proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120']]);
        $path = $proof->store($data['proof']);

        try {
            $lifecycle->submitPayment($order, $path);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);

            throw $exception;
        }

        return redirect()->route('store.orders.show', ['order' => $order->public_id])
            ->with('status', 'Bukti pembayaran diterima dan akan diperiksa.');
    }

    public function ticket(Request $request, Order $order): RedirectResponse
    {
        Gate::authorize('view', $order);
        $data = $request->validate([
            'kind' => ['required', 'in:support,return'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        if ($data['kind'] === 'return' && ! in_array($order->status, ['shipped', 'completed'], true)) {
            throw ValidationException::withMessages(['kind' => 'Retur dapat diajukan setelah pesanan dikirim atau diambil.']);
        }

        $order->tickets()->create($data + ['status' => 'open']);

        return redirect()->route('store.orders.show', ['order' => $order->public_id])->with('status', 'Tiket berhasil dikirim.');
    }
}
