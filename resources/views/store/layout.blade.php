<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/webp" href="{{ asset('images/brand/jsit-indonesia.webp') }}">
    <meta name="theme-color" content="#23473C">
    <meta name="description" content="@yield('description', 'Belanja batik, seragam, dan perlengkapan sekolah pilihan di Koperasi JSIT.')">
    <title>@hasSection('title')@yield('title') &middot; Koperasi JSIT @else Koperasi JSIT &middot; Batik & Perlengkapan Sekolah @endif</title>
    @vite(['resources/css/store.css', 'resources/js/store.js'])
    @livewireStyles
</head>
<body>
    @include('shared.loading-feedback')
    <a class="skip-link" href="#main">Langsung ke konten</a>
    <div class="announcement">
        <div class="shell announcement-inner">
            <span>@include('store.partials.icon', ['name' => 'truck']) Pengiriman & ambil di toko</span>
            <a href="{{ route('store.orders.lookup') }}">Lacak Pesanan @include('store.partials.icon', ['name' => 'arrow'])</a>
        </div>
    </div>
    <header class="site-header">
        <div class="shell header-inner">
            <button type="button" class="icon-button menu-toggle" data-menu-toggle aria-label="Menu utama" aria-controls="mobile-nav" aria-expanded="false">@include('store.partials.icon', ['name' => 'menu'])</button>
            @include('store.partials.brand')
            <nav class="desktop-nav" aria-label="Navigasi utama">
                <a href="{{ route('store.home') }}" @if(request()->routeIs('store.home')) aria-current="page" @endif>Beranda</a>
                <a href="{{ route('store.catalog') }}" @if(request()->routeIs('store.catalog', 'store.product')) aria-current="page" @endif>Katalog</a>
                <a href="{{ route('store.membership') }}" @if(request()->routeIs('store.membership')) aria-current="page" @endif>Member Sekolah</a>
                <a href="{{ route('store.faq') }}" @if(request()->routeIs('store.faq', 'store.contact')) aria-current="page" @endif>Bantuan</a>
            </nav>
            <div class="header-actions">
                <button type="button" class="icon-button search-toggle" data-search-toggle aria-label="Cari produk" aria-controls="header-search" aria-expanded="false">@include('store.partials.icon', ['name' => 'search'])</button>
                @if(Route::has('login'))
                    <a class="icon-button account-link" href="{{ auth()->check() ? route('profile.edit') : route('login') }}" aria-label="{{ auth()->check() ? 'Akun dan pesanan saya' : 'Masuk' }}">@include('store.partials.icon', ['name' => 'user'])</a>
                @endif
                @php($cartCount = array_sum(session('store.cart', [])))
                <a class="icon-button bag-link" href="{{ route('store.cart') }}" aria-label="Keranjang belanja{{ $cartCount ? ', '.$cartCount.' barang' : '' }}">@include('store.partials.icon', ['name' => 'bag']) @if($cartCount)<span class="cart-count" aria-hidden="true">{{ min($cartCount, 99) }}</span>@endif</a>
            </div>
        </div>
        <div id="header-search" class="header-panel" hidden>
            @livewire('catalog-search', ['initial' => is_string(request('q')) ? request()->string('q')->limit(100)->toString() : ''])
        </div>
        <nav id="mobile-nav" class="header-panel mobile-nav" aria-label="Navigasi seluler" hidden>
            <div class="shell">
                <a href="{{ route('store.home') }}">Beranda</a>
                <a href="{{ route('store.catalog') }}">Katalog</a>
                <a href="{{ route('store.membership') }}">Member Sekolah</a>
                <a href="{{ route('store.faq') }}">Bantuan</a>
                @if(Route::has('login'))<a href="{{ auth()->check() ? route('profile.edit') : route('login') }}">{{ auth()->check() ? 'Akun & Pesanan Saya' : 'Masuk' }}</a>@endif
            </div>
        </nav>
    </header>

    @if(session('status') || session('success') || session('error') || $errors->any())
        <div class="shell notices">
            @if(session('status') || session('success'))
                <div class="notice notice-success" role="status">@include('store.partials.icon', ['name' => 'check']) <span>{{ session('success') ?? session('status') }}</span></div>
            @endif
            @if(session('error'))<div class="notice notice-error" role="alert">{{ session('error') }}</div>@endif
            @if($errors->any())
                <div class="notice notice-error" role="alert"><div><strong>Periksa kembali isian Anda.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
            @endif
        </div>
    @endif

    <main id="main" tabindex="-1">@yield('content')</main>

    <footer class="site-footer">
        <div class="shell footer-main">
            <div class="footer-brand">@include('store.partials.brand')<p>Batik dan perlengkapan sekolah untuk keluarga besar Jaringan Sekolah Islam Terpadu.</p></div>
            <div><h2>Belanja</h2><a href="{{ route('store.catalog') }}">Katalog produk</a><a href="{{ route('store.membership') }}">Member sekolah</a></div>
            <div><h2>Bantuan</h2><a href="{{ route('store.orders.lookup') }}">Lacak pesanan</a><a href="{{ route('store.faq') }}">Pertanyaan umum</a><a href="{{ route('store.contact') }}">Hubungi kami</a></div>
            <div><h2>Pesanan Anda</h2><a href="{{ route('store.cart') }}">Keranjang</a>@auth<a href="{{ route('store.orders.index') }}">Riwayat pesanan</a>@endauth</div>
        </div>
        <div class="shell footer-bottom"><span>&copy; {{ date('Y') }} Koperasi JSIT</span><span>Indonesia</span></div>
    </footer>
    @livewireScripts
</body>
</html>
