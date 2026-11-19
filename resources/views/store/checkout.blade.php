@extends('store.layout')
@section('title', 'Checkout')

@section('content')
    @php
        $money = fn ($amount) => 'Rp'.number_format($amount, 0, ',', '.');
        $lead = $lines->max(fn ($line) => $line['variant']->preorder ? $line['variant']->lead_days : 0);
        $customer = auth()->user();
        $selectedRate = $rates->firstWhere('id', (int) old('shipping_rate_id'));
    @endphp
    @include('store.partials.page-intro', ['heading' => 'Checkout', 'trail' => ['Keranjang' => route('store.cart'), 'Checkout' => null]])
    <div class="shell page-body">
        <form method="POST" action="{{ route('store.checkout.place') }}" class="transaction-grid" data-checkout data-subtotal="{{ $subtotal }}">
            @csrf
            <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', $idempotencyKey) }}">
            <div class="checkout-fields">
                @guest
                    @if(Route::has('login'))<div class="notice notice-info checkout-login">Sudah punya akun? <a class="plain-link" href="{{ route('login') }}">Masuk</a> atau lanjut sebagai tamu.</div>@endif
                @endguest
                <section class="panel form-panel" aria-labelledby="buyer-details">
                    <h2 id="buyer-details">Kontak Pembeli</h2>
                    <div class="form-grid">
                        <div class="field wide"><label for="checkout-name">Nama lengkap</label><input id="checkout-name" name="name" autocomplete="name" value="{{ old('name', $customer?->name) }}" required></div>
                        <div class="field"><label for="checkout-email">Email</label><input id="checkout-email" name="email" type="email" autocomplete="email" value="{{ old('email', $customer?->email) }}" required></div>
                        <div class="field"><label for="checkout-phone">Nomor ponsel</label><input id="checkout-phone" name="phone" type="tel" autocomplete="tel" value="{{ old('phone', $customer?->phone) }}" required></div>
                    </div>
                    <p class="field-help">Tautan pesanan dikirim ke email ini.</p>
                </section>
                <section class="panel form-panel" aria-labelledby="shipping-title">
                    <h2 id="shipping-title">Cara Menerima Pesanan</h2>
                    @if($rates->isEmpty())<p class="notice notice-info">Saat ini belum ada metode pengiriman atau ambil di toko yang tersedia.</p>@endif
                    <fieldset class="shipping-rates">
                        <legend class="sr-only">Pilih pengiriman atau ambil di toko</legend>
                        @foreach($rates as $rate)
                            <label class="rate-option">
                                <input type="radio" name="shipping_rate_id" value="{{ $rate->id }}" data-rate data-pickup="{{ $rate->pickup ? 'true' : 'false' }}" data-region="{{ $rate->region }}" data-fee="{{ $rate->price }}" @checked($selectedRate?->id === $rate->id) required>
                                <span class="rate-icon">@include('store.partials.icon', ['name' => $rate->pickup ? 'pin' : 'truck'])</span>
                                <span class="rate-text"><strong>{{ $rate->name }}</strong>@if(!$rate->pickup && $rate->region)<small>{{ $rate->region }}</small>@endif</span>
                                <strong>{{ $rate->price > 0 ? $money($rate->price) : 'Gratis' }}</strong>
                            </label>
                        @endforeach
                    </fieldset>
                    <div class="form-grid address-fields" data-address @if($selectedRate?->pickup) hidden @endif>
                        <div class="field wide"><label for="checkout-address">Alamat lengkap</label><textarea id="checkout-address" name="address_line" rows="3" autocomplete="street-address" data-delivery-required>{{ old('address_line') }}</textarea></div>
                        <div class="field"><label for="checkout-province">Provinsi</label><input id="checkout-province" name="province" autocomplete="address-level1" value="{{ old('province', $selectedRate?->region) }}" data-province data-delivery-required></div>
                        <div class="field"><label for="checkout-city">Kota / kabupaten</label><input id="checkout-city" name="city" autocomplete="address-level2" value="{{ old('city') }}" data-delivery-required></div>
                        <div class="field"><label for="checkout-postal">Kode pos</label><input id="checkout-postal" name="postal_code" inputmode="numeric" autocomplete="postal-code" value="{{ old('postal_code') }}" data-delivery-required></div>
                    </div>
                    @php($contact = $settings->get('contact', []))
                    @if(filled($contact['address'] ?? null))<p class="summary-note pickup-note" data-pickup-note @unless($selectedRate?->pickup) hidden @endunless>@include('store.partials.icon', ['name' => 'pin']) <span>Lokasi pengambilan: {{ $contact['address'] }}</span></p>@endif
                </section>
                <section class="panel form-panel" aria-labelledby="payment-heading">
                    <h2 id="payment-heading">Pembayaran</h2>
                    <div class="payment-option">@include('store.partials.icon', ['name' => 'card']) <div><strong>Transfer bank manual</strong><p>Setelah pesanan dibuat, kami tampilkan rekening tujuan dan tempat unggah bukti transfer.</p></div></div>
                </section>
            </div>
            <aside class="panel summary-panel" aria-labelledby="checkout-summary-title">
                <h2 id="checkout-summary-title">Ringkasan Pesanan</h2>
                <ul class="summary-items">
                    @foreach($lines as $line)<li><span><strong>{{ $line['variant']->product->name }}</strong><small>{{ $line['variant']->name }} × {{ $line['quantity'] }}</small></span><strong>{{ $money($line['subtotal']) }}</strong></li>@endforeach
                </ul>
                <dl class="totals">
                    <div><dt>Subtotal</dt><dd>{{ $money($subtotal) }}</dd></div>
                    <div><dt>Ongkos kirim</dt><dd data-shipping-fee>{{ $selectedRate ? $money($selectedRate->price) : 'Pilih metode' }}</dd></div>
                    <div class="total-final"><dt>Total</dt><dd data-order-total aria-live="polite">{{ $money($subtotal + ($selectedRate?->price ?? 0)) }}</dd></div>
                </dl>
                @if($lead)<div class="summary-note">@include('store.partials.icon', ['name' => 'clock']) <p>Estimasi persiapan <strong>{{ $lead }} hari</strong>. Semua barang dikirim atau diambil bersamaan.</p></div>@endif
                <button class="button button-primary button-block" type="submit" @disabled($rates->isEmpty())>Buat Pesanan @include('store.partials.icon', ['name' => 'arrow'])</button>
            </aside>
        </form>
    </div>
@endsection
