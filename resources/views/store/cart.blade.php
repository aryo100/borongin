@extends('store.layout')
@section('title', 'Keranjang Belanja')

@section('content')
    @include('store.partials.page-intro', ['heading' => 'Keranjang Belanja', 'trail' => ['Keranjang' => null]])
    <div class="shell page-body">
        @if($lines->isNotEmpty())
            @php
                $money = fn ($amount) => 'Rp'.number_format($amount, 0, ',', '.');
                $lead = $lines->max(fn ($line) => $line['variant']->preorder ? $line['variant']->lead_days : 0);
            @endphp
            <div class="transaction-grid">
                <section class="panel cart-lines" aria-label="Barang di keranjang">
                    <h2>Barang Anda <span class="count-label">{{ $lines->sum('quantity') }}</span></h2>
                    @foreach($lines as $line)
                        @php
                            $variant = $line['variant'];
                            $product = $variant->product;
                            $image = $product->images->sortBy('position')->first();
                        @endphp
                        <article class="cart-line">
                            <a class="cart-image" href="{{ route('store.product', $product->slug) }}" tabindex="-1" aria-hidden="true"><img src="@include('store.partials.image-url', ['path' => $image?->path])" alt="" width="86" height="100"></a>
                            <div class="cart-line-detail">
                                <h3><a href="{{ route('store.product', $product->slug) }}">{{ $product->name }}</a></h3>
                                <p>{{ $variant->name }} · {{ $money($variant->price) }} per item</p>
                                <span class="status-pill">{{ $variant->preorder ? 'Pre-order · '.$variant->lead_days.' hari' : 'Ready stock' }}</span>
                            </div>
                            <div class="cart-line-edit">
                                <form method="POST" action="{{ route('store.cart.update', $variant) }}" class="cart-edit-form">
                                    @csrf @method('PATCH')
                                    <label for="cart-qty-{{ $variant->id }}" class="sr-only">Jumlah {{ $product->name }} {{ $variant->name }}</label>
                                    <div class="quantity-control" data-quantity><button type="button" data-step="-1" aria-label="Kurangi jumlah">@include('store.partials.icon', ['name' => 'minus'])</button><input id="cart-qty-{{ $variant->id }}" name="quantity" type="number" min="1" max="{{ \App\Services\ShoppingCart::MAX_QUANTITY }}" inputmode="numeric" value="{{ $line['quantity'] }}" required><button type="button" data-step="1" aria-label="Tambah jumlah">@include('store.partials.icon', ['name' => 'plus'])</button></div>
                                    <button class="plain-link" type="submit">Simpan</button>
                                </form>
                                <form method="POST" action="{{ route('store.cart.remove', $variant) }}">@csrf @method('DELETE')<button type="submit" class="plain-link danger-link" aria-label="Hapus {{ $product->name }} {{ $variant->name }}">Hapus</button></form>
                            </div>
                            <strong class="cart-line-price">{{ $money($line['subtotal']) }}</strong>
                        </article>
                    @endforeach
                </section>
                <aside class="panel summary-panel" aria-labelledby="cart-summary-title">
                    <h2 id="cart-summary-title">Ringkasan</h2>
                    <dl class="totals"><div><dt>Subtotal</dt><dd>{{ $money($subtotal) }}</dd></div><div><dt>Ongkos kirim</dt><dd>Dihitung saat checkout</dd></div></dl>
                    @if($lead)
                        <div class="summary-note">@include('store.partials.icon', ['name' => 'clock']) <p>Semua barang disiapkan bersama. Estimasi persiapan <strong>{{ $lead }} hari</strong> sesuai pre-order terlama.</p></div>
                    @endif
                    <a class="button button-primary button-block" href="{{ route('store.checkout') }}">Lanjut ke Checkout @include('store.partials.icon', ['name' => 'arrow'])</a>
                    <a class="text-link continue-shopping" href="{{ route('store.catalog') }}">@include('store.partials.icon', ['name' => 'back']) Lanjut belanja</a>
                </aside>
            </div>
        @else
            <div class="empty-state"><span class="empty-icon">@include('store.partials.icon', ['name' => 'bag'])</span><h2>Keranjang Anda kosong</h2><p>Jelajahi katalog dan tambahkan produk yang Anda sukai.</p><a href="{{ route('store.catalog') }}" class="button button-primary">Jelajahi Katalog @include('store.partials.icon', ['name' => 'arrow'])</a></div>
        @endif
    </div>
@endsection
