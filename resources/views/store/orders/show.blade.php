@extends('store.layout')
@section('title', 'Pesanan '.$order->public_id)

@section('content')
    @php
        $money = fn ($amount) => 'Rp'.number_format($amount, 0, ',', '.');
        $bank = $settings->get('bank', []);
        $contact = $settings->get('contact', []);
        $address = $order->address ?? [];
        $canUpload = $order->status === 'awaiting_payment' && (!$order->payment_due_at || $order->payment_due_at->isFuture());
        $paymentLabels = ['pending' => 'Sedang ditinjau', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'];
        $ticketLabels = ['open' => 'Terbuka', 'answered' => 'Dijawab', 'closed' => 'Ditutup'];
    @endphp
    @include('store.partials.page-intro', ['heading' => 'Detail Pesanan', 'trail' => ['Detail Pesanan' => null]])
    <div class="shell page-body">
        <div class="order-heading"><div><p class="eyebrow">Nomor pesanan</p><h2>{{ $order->public_id }}</h2><p>{{ $order->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j F Y, H.i') }} WIB</p></div>@include('store.partials.order-status', ['status' => $order->status])</div>
        <div class="transaction-grid order-grid">
            <div class="checkout-fields">
                @if(in_array($order->status, ['awaiting_payment', 'payment_review'], true) || $order->payments->isNotEmpty())
                    <section class="panel form-panel" aria-labelledby="order-payment">
                        <h2 id="order-payment">Pembayaran</h2>
                        @if($canUpload)
                            <p class="panel-description">Transfer tepat <strong>{{ $money($order->total) }}</strong> dan unggah bukti pembayaran untuk verifikasi.</p>
                            @if(filled($bank['number'] ?? null))
                                <div class="bank-details">@include('store.partials.icon', ['name' => 'card'])<div><small>{{ $bank['name'] ?? 'Transfer bank' }}</small><strong>{{ $bank['number'] }}</strong><small>Atas nama {{ $bank['holder'] ?? 'Koperasi JSIT' }}</small></div></div>
                            @else
                                <div class="notice notice-info">Hubungi Koperasi JSIT untuk informasi rekening. <a href="{{ route('store.contact') }}" class="plain-link">Hubungi kami</a></div>
                            @endif
                            @if($order->payment_due_at)<p class="field-help">Batas pembayaran {{ $order->payment_due_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j F Y, H.i') }} WIB.</p>@endif
                            <form method="POST" action="{{ route('store.orders.payment', $order->public_id) }}" class="stack-form upload-form" enctype="multipart/form-data">
                                @csrf
                                <div class="field"><label for="order-proof">Bukti transfer</label><input id="order-proof" name="proof" type="file" accept="image/jpeg,image/png,image/webp" aria-describedby="proof-help" required><small id="proof-help" class="muted">JPG, PNG, atau WebP hingga 5 MB.</small></div>
                                <button class="button button-primary" type="submit">@include('store.partials.icon', ['name' => 'upload']) Unggah Bukti Transfer</button>
                            </form>
                        @elseif($order->status === 'payment_review')
                            <p class="notice notice-info">Bukti transfer diterima dan sedang diperiksa.</p>
                        @endif
                        @if($order->payments->isNotEmpty())
                            <ul class="activity-list" aria-label="Riwayat pembayaran">
                                @foreach($order->payments->sortByDesc('id') as $payment)<li><div><strong>{{ $paymentLabels[$payment->status] ?? $payment->status }}</strong><small>{{ $payment->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j F Y, H.i') }} WIB</small>@if($payment->note)<p>{{ $payment->note }}</p>@endif</div></li>@endforeach
                            </ul>
                        @endif
                    </section>
                @endif

                <section class="panel form-panel" aria-labelledby="delivery-detail">
                    <h2 id="delivery-detail">Pengiriman & Kontak</h2>
                    <dl class="order-delivery">
                        <div><dt>Penerima</dt><dd>{{ $order->name }}<br>{{ $order->phone }}</dd></div>
                        <div><dt>Metode</dt><dd>{{ $order->shipping_name }}</dd></div>
                        @if($address)<div><dt>Alamat</dt><dd>{{ $address['address_line'] ?? '' }}<br>{{ implode(', ', array_filter([$address['city'] ?? '', $address['province'] ?? '', $address['postal_code'] ?? ''])) }}</dd></div>
                        @elseif(filled($contact['address'] ?? null))<div><dt>Lokasi pengambilan</dt><dd>{{ $contact['address'] }}</dd></div>@endif
                        @if($order->tracking_number)<div><dt>Nomor resi</dt><dd><strong>{{ $order->tracking_number }}</strong></dd></div>@endif
                        @if($order->eta_days > 0)<div><dt>Estimasi persiapan</dt><dd>{{ $order->eta_days }} hari</dd></div>@endif
                    </dl>
                </section>

                <section class="panel form-panel" aria-labelledby="order-help">
                    <h2 id="order-help">Bantuan Pesanan</h2>
                    @if($order->tickets->isNotEmpty())
                        <ul class="activity-list" aria-label="Tiket bantuan">
                            @foreach($order->tickets->sortByDesc('id') as $ticket)
                                <li><div><strong>{{ $ticket->subject }}</strong><p>{{ $ticket->message }}</p>@if($ticket->response)<p class="ticket-response"><strong>Balasan Koperasi JSIT</strong><br>{{ $ticket->response }}</p>@endif</div><span class="status-pill">{{ $ticketLabels[$ticket->status] ?? $ticket->status }}</span></li>
                            @endforeach
                        </ul>
                    @endif
                    <details class="ticket-disclosure" @if(old('message')) open @endif>
                        <summary>Buat tiket bantuan @include('store.partials.icon', ['name' => 'down'])</summary>
                        <form method="POST" action="{{ route('store.orders.tickets', $order->public_id) }}" class="stack-form">
                            @csrf
                            <div class="field"><label for="ticket-kind">Jenis bantuan</label><select id="ticket-kind" name="kind"><option value="support" @selected(old('kind') === 'support')>Pertanyaan atau keluhan</option>@if(in_array($order->status, ['shipped', 'completed'], true))<option value="return" @selected(old('kind') === 'return')>Pengembalian</option>@endif</select></div>
                            <div class="field"><label for="ticket-subject">Subjek</label><input id="ticket-subject" name="subject" value="{{ old('subject') }}" maxlength="150" required></div>
                            <div class="field"><label for="ticket-message">Pesan</label><textarea id="ticket-message" name="message" rows="4" maxlength="3000" required>{{ old('message') }}</textarea></div>
                            <button class="button button-primary" type="submit">Kirim Tiket @include('store.partials.icon', ['name' => 'arrow'])</button>
                        </form>
                    </details>
                </section>
            </div>
            <aside class="panel summary-panel" aria-labelledby="order-summary-title">
                <h2 id="order-summary-title">Ringkasan Pesanan</h2>
                <ul class="summary-items">@foreach($order->items as $item)<li><span><strong>{{ $item->product_name }}</strong><small>{{ $item->variant_name }} × {{ $item->quantity }}</small></span><strong>{{ $money($item->unit_price * $item->quantity) }}</strong></li>@endforeach</ul>
                <dl class="totals"><div><dt>Subtotal</dt><dd>{{ $money($order->subtotal) }}</dd></div><div><dt>Ongkos kirim</dt><dd>{{ $money($order->shipping_fee) }}</dd></div><div class="total-final"><dt>Total</dt><dd>{{ $money($order->total) }}</dd></div></dl>
                <a href="{{ route('store.orders.invoice', $order->public_id) }}" class="button button-secondary button-block">@include('store.partials.icon', ['name' => 'receipt']) Unduh Invoice</a>
                @guest<form method="POST" action="{{ route('store.orders.resend', $order->public_id) }}" class="resend-form">@csrf<button class="plain-link" type="submit">Kirim ulang tautan ke email</button></form>@endguest
            </aside>
        </div>
    </div>
@endsection
