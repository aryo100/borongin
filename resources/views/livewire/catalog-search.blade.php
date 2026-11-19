<div class="shell">
    <form method="GET" action="{{ route('store.catalog') }}" class="header-search" role="search">
        <label class="sr-only" for="header-query">Cari produk</label>
        <input id="header-query" name="q" type="search" wire:model.live.debounce.300ms="query" placeholder="Cari batik, buku, atau seragam…" maxlength="100" required>
        <button type="submit" class="button button-primary">Cari</button>
    </form>
    <p class="search-loading" role="status" aria-live="polite" wire:loading.delay wire:target="query">
        <span class="loading-spinner" aria-hidden="true"></span>Mencari produk…
    </p>
    @if ($suggestions->isNotEmpty())
        <ul class="pb-4" aria-label="Saran produk">
            @foreach ($suggestions as $product)
                <li class="py-1"><a class="text-link" href="{{ route('store.product', $product->slug) }}">{{ $product->name }}</a></li>
            @endforeach
        </ul>
    @endif
</div>
