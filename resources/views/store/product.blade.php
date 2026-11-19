@extends('store.layout')
@section('title', $product->name)

@section('content')
    @php
        $images = $product->images->sortBy('position')->values();
        $cover = $images->first();
        $variants = $product->variants->where('active', true)->values();
        $available = $variants->filter(fn ($variant) => $variant->available > 0);
        $maxQuantity = \App\Services\ShoppingCart::MAX_QUANTITY;
        $selected = $available->firstWhere('id', (int) old('variant_id')) ?? $available->first() ?? $variants->first();
        $canBuy = !$product->members_only || $isMember;
        $money = fn ($amount) => 'Rp'.number_format($amount, 0, ',', '.');
    @endphp
    <div class="shell product-page">
        @php
            $trail = ['Katalog' => route('store.catalog')];
            if ($product->category) {
                $trail[$product->category->name] = route('store.catalog', ['category' => $product->category->slug]);
            }
            $trail[$product->name] = null;
        @endphp
        @include('store.partials.breadcrumb', ['trail' => $trail])
        <div class="product-detail">
            <div class="product-gallery" data-gallery>
                <div class="gallery-main"><img data-gallery-main src="@include('store.partials.image-url', ['path' => $cover?->path])" alt="{{ $cover?->alt ?: $product->name }}" width="700" height="750" fetchpriority="high"></div>
                @if($images->count() > 1)
                    <div class="gallery-thumbs" aria-label="Galeri produk">
                        @foreach($images as $image)
                            <button type="button" class="gallery-thumb" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" aria-label="Tampilkan foto {{ $loop->iteration }}" data-gallery-thumb><img src="@include('store.partials.image-url', ['path' => $image->path])" alt="{{ $image->alt ?: $product->name }}" loading="lazy" width="88" height="88"></button>
                        @endforeach
                    </div>
                @endif
            </div>

            <section class="product-info" aria-labelledby="product-name" data-product>
                @if($product->category)<a class="eyebrow product-category-link" href="{{ route('store.catalog', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a>@endif
                <h1 id="product-name">{{ $product->name }}</h1>
                @if($selected)<div class="product-price" data-variant-price aria-live="polite">{{ $money($selected->price) }}</div>@endif
                @if($product->members_only)<p class="badge product-member-badge">Khusus Member Sekolah</p>@endif

                @if($product->description)<p class="product-description">{{ $product->description }}</p>@endif

                @if($variants->isNotEmpty())
                    <form method="POST" action="{{ route('store.cart.add') }}" class="product-form">
                        @csrf
                        <label for="product-variant">Pilih varian</label>
                        <select id="product-variant" name="variant_id" data-variant-select>
                            @foreach($variants as $variant)
                                @php($stock = max(0, min($variant->available, $maxQuantity)))
                                @php($fulfillment = !$stock ? 'Stok habis' : ($variant->preorder ? 'Pre-order · '.$variant->lead_days.' hari' : 'Ready stock'))
                                <option value="{{ $variant->id }}" data-price="{{ $money($variant->price) }}" data-stock="{{ $stock }}" data-fulfillment="{{ $fulfillment }}" data-sku="{{ $variant->sku }}" @selected($selected?->id === $variant->id)>{{ $variant->name }} · {{ $money($variant->price) }} · {{ $fulfillment }}</option>
                            @endforeach
                        </select>
                        <div class="fulfillment-row">
                            <span class="status-pill" data-variant-fulfillment>{{ $selected && $selected->available > 0 ? ($selected->preorder ? 'Pre-order · '.$selected->lead_days.' hari' : 'Ready stock') : 'Stok habis' }}</span>
                            <span class="muted" data-variant-sku>{{ $selected?->sku }}</span>
                        </div>
                        @if($canBuy && $available->isNotEmpty())
                            <div class="add-to-cart">
                                <div><label for="product-quantity">Jumlah</label><div class="quantity-control" data-quantity><button type="button" data-step="-1" aria-label="Kurangi jumlah">@include('store.partials.icon', ['name' => 'minus'])</button><input id="product-quantity" name="quantity" type="number" min="1" max="{{ min($selected->available, $maxQuantity) }}" value="{{ old('quantity', 1) }}" inputmode="numeric" required><button type="button" data-step="1" aria-label="Tambah jumlah">@include('store.partials.icon', ['name' => 'plus'])</button></div></div>
                                <button class="button button-primary" type="submit" data-add-to-cart>@include('store.partials.icon', ['name' => 'bag']) Tambah ke Keranjang</button>
                            </div>
                        @elseif(!$canBuy)
                            <div class="notice notice-info">Produk ini dapat dipesan oleh member sekolah yang sudah disetujui.</div>
                            <a class="button button-primary" href="{{ route('store.membership') }}">Info Member Sekolah @include('store.partials.icon', ['name' => 'arrow'])</a>
                        @else
                            <a class="button button-secondary" href="{{ route('store.catalog') }}">Lihat produk lain</a>
                        @endif
                    </form>
                @else
                    <div class="notice notice-info">Produk ini belum tersedia untuk dipesan.</div>
                @endif
                <div class="product-service">
                    <div>@include('store.partials.icon', ['name' => 'truck']) Pengiriman atau ambil di toko</div>
                    <div>@include('store.partials.icon', ['name' => 'shield']) Pembayaran melalui transfer bank</div>
                </div>
            </section>
        </div>
    </div>
@endsection
