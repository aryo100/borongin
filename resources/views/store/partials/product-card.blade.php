@php
    $activeVariants = $product->variants->where('active', true);
    $available = $activeVariants->filter(fn ($variant) => $variant->available > 0);
    $price = ($available->isNotEmpty() ? $available : $activeVariants)->min('price');
    $image = $product->images->sortBy('position')->first();
    $label = $product->members_only ? 'Khusus Member' : ($available->isEmpty() ? 'Stok Habis' : ($available->where('preorder', false)->isNotEmpty() ? 'Ready Stock' : 'Pre-order'));
@endphp
<article class="product-card">
    <a href="{{ route('store.product', $product->slug) }}" class="product-card-link">
        <div class="product-card-image">
            <img src="@include('store.partials.image-url', ['path' => $image?->path])" alt="{{ $image?->alt ?: $product->name }}" loading="lazy" decoding="async" width="540" height="590">
            <span class="badge {{ $available->isEmpty() && !$product->members_only ? 'badge-muted' : '' }}">{{ $label }}</span>
            <span class="product-card-arrow" aria-hidden="true">@include('store.partials.icon', ['name' => 'arrow'])</span>
        </div>
        <div class="product-card-copy">
            @if($product->category)<span class="product-category">{{ $product->category->name }}</span>@endif
            <h3>{{ $product->name }}</h3>
            @if($price !== null)<p>{{ $activeVariants->pluck('price')->unique()->count() > 1 ? 'Mulai Rp' : 'Rp' }}{{ number_format($price, 0, ',', '.') }}</p>@endif
        </div>
    </a>
</article>
