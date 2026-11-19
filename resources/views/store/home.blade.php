@extends('store.layout')

@section('content')
    @php
        $banner = $settings->get('banner', []);
        $showCustomBanner = $banner['active'] ?? true;
        $heroImage = $showCustomBanner && filled($banner['image_path'] ?? null) ? $banner['image_path'] : 'images/brand/jsit-school-batik-hero.webp';
    @endphp
    <div class="shell home-top">
        <section class="hero" aria-labelledby="hero-title">
            <picture class="hero-picture">
                @if($heroImage === 'images/brand/jsit-school-batik-hero.webp')<source media="(max-width: 700px)" srcset="{{ asset('images/brand/jsit-school-batik-mobile.webp') }}">@endif
                <img class="hero-image" src="@include('store.partials.image-url', ['path' => $heroImage])" alt="" fetchpriority="high" width="1923" height="818">
            </picture>
            <div class="hero-shade"></div>
            <div class="hero-content">
                <div class="eyebrow hero-eyebrow">@include('store.partials.icon', ['name' => 'spark']) Koperasi Jaringan Sekolah Islam Terpadu</div>
                @if($showCustomBanner && filled($banner['title'] ?? null))
                    <h1 id="hero-title">{{ $banner['title'] }}</h1>
                @else
                    <h1 id="hero-title">Batik pilihan untuk<br> sekolah Islam terpadu.</h1>
                @endif
                <p>{{ $showCustomBanner && filled($banner['description'] ?? null) ? $banner['description'] : 'Seragam batik JSIT, buku, dan perlengkapan belajar untuk mendukung kegiatan sekolah Anda.' }}</p>
                <a href="{{ route('store.catalog') }}" class="button button-primary">{{ $showCustomBanner && filled($banner['cta'] ?? null) ? $banner['cta'] : 'Jelajahi Koleksi' }} @include('store.partials.icon', ['name' => 'arrow'])</a>
            </div>
        </section>

        <div class="service-strip" aria-label="Layanan Koperasi JSIT">
            <div>@include('store.partials.icon', ['name' => 'truck']) <span>Pengiriman sesuai wilayah</span></div>
            <div>@include('store.partials.icon', ['name' => 'pin']) <span>Ambil pesanan di toko</span></div>
            <div>@include('store.partials.icon', ['name' => 'shield']) <span>Pembayaran transfer bank</span></div>
        </div>
    </div>

    @if($categories->isNotEmpty())
        <section class="shell home-section categories-section" aria-labelledby="categories-title">
            <div class="section-heading">
                <div><h2 id="categories-title">Belanja per kategori</h2></div>
                <a class="text-link" href="{{ route('store.catalog') }}">Lihat katalog @include('store.partials.icon', ['name' => 'arrow'])</a>
            </div>
            <div class="category-grid">
                @foreach($categories as $category)
                    <a href="{{ route('store.catalog', ['category' => $category->slug]) }}" class="category-card">
                        <div class="category-card-image"><img src="@include('store.partials.image-url', ['path' => $category->image_path ?: 'images/catalog/cat-'.$category->slug.'.jpg'])" loading="lazy" decoding="async" alt="" width="400" height="300"></div>
                        <div>{{ $category->name }} @include('store.partials.icon', ['name' => 'chevron'])</div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if($featured->isNotEmpty())
        <section class="featured-section" aria-labelledby="featured-title">
            <div class="shell home-section">
                <div class="section-heading">
                    <div><h2 id="featured-title">Pilihan Koperasi JSIT</h2><p>Temukan produk yang kami pilih untuk Anda.</p></div>
                    <a class="text-link" href="{{ route('store.catalog') }}">Lihat semua produk @include('store.partials.icon', ['name' => 'arrow'])</a>
                </div>
                <div class="product-grid">
                    @foreach($featured as $product) @include('store.partials.product-card') @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="shell home-section" aria-labelledby="school-title">
        <div class="school-callout">
            <div><div class="eyebrow">Member Sekolah</div><h2 id="school-title">Perlengkapan untuk sekolah Anda.</h2><p>Daftarkan sekolah Anda untuk mengakses koleksi khusus member.</p><a class="button button-light" href="{{ route('store.membership') }}">Info Member Sekolah @include('store.partials.icon', ['name' => 'arrow'])</a></div>
            <img class="school-callout-image" src="{{ asset('images/brand/jsit-school-batik-mobile.webp') }}" alt="Anak-anak sekolah mengenakan seragam batik JSIT" width="768" height="512" loading="lazy">
        </div>
    </section>
@endsection
