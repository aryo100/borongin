@extends('store.layout')
@section('title', 'Katalog Produk')

@section('content')
    @php($category = $categories->firstWhere('slug', $selectedCategory))
    <div class="page-intro catalog-intro">
        <div class="shell">
            @include('store.partials.breadcrumb', ['trail' => ['Katalog' => null]])
            <h1>{{ $category?->name ?? 'Katalog Produk' }}</h1>
            @if($category?->description)<p>{{ $category->description }}</p>@endif
        </div>
    </div>
    <div class="shell catalog-body">
        <aside class="catalog-aside" aria-label="Filter produk">
            <h2>Kategori</h2>
            <nav aria-label="Kategori produk">
                <a href="{{ route('store.catalog', array_filter(['q' => $search])) }}" @if(!$selectedCategory) aria-current="page" @endif>Semua kategori @include('store.partials.icon', ['name' => 'chevron'])</a>
                @foreach($categories as $option)
                    <a href="{{ route('store.catalog', array_filter(['category' => $option->slug, 'q' => $search])) }}" @if($selectedCategory === $option->slug) aria-current="page" @endif>{{ $option->name }} @include('store.partials.icon', ['name' => 'chevron'])</a>
                @endforeach
            </nav>
        </aside>
        <section class="catalog-results" aria-label="Hasil pencarian produk">
            <form action="{{ route('store.catalog') }}" method="GET" class="catalog-search" role="search">
                <div class="input-with-icon">@include('store.partials.icon', ['name' => 'search'])<label class="sr-only" for="catalog-q">Cari produk</label><input id="catalog-q" name="q" type="search" value="{{ $search }}" placeholder="Cari di katalog…"></div>
                <label class="sr-only" for="catalog-category">Kategori</label>
                <select id="catalog-category" name="category">
                    <option value="">Semua kategori</option>
                    @foreach($categories as $option)<option value="{{ $option->slug }}" @selected($selectedCategory === $option->slug)>{{ $option->name }}</option>@endforeach
                </select>
                <button class="button button-primary" type="submit">Cari</button>
            </form>
            <div class="result-info">
                <p>{{ $products->total() }} produk @if(filled($search)) untuk “{{ $search }}” @endif</p>
                @if(filled($search))<a href="{{ route('store.catalog', array_filter(['category' => $selectedCategory])) }}" class="text-link">Hapus pencarian</a>@endif
            </div>
            @if($products->isNotEmpty())
                <div class="product-grid catalog-grid">@foreach($products as $product) @include('store.partials.product-card') @endforeach</div>
                @include('store.partials.pagination', ['paginator' => $products])
            @else
                <div class="empty-state"><span class="empty-icon">@include('store.partials.icon', ['name' => 'search'])</span><h2>Produk belum ditemukan</h2><p>Coba kata kunci atau kategori lain.</p><a class="button button-secondary" href="{{ route('store.catalog') }}">Lihat semua produk</a></div>
            @endif
        </section>
    </div>
@endsection
