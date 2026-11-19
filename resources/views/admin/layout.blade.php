<!doctype html>
<html lang="id" data-bs-theme="light" data-lte-color-mode="off">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dasbor') | Admin Koperasi JSIT</title>
    <link rel="icon" type="image/webp" href="{{ asset('images/brand/jsit-indonesia.webp') }}">
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="borongin-admin layout-fixed sidebar-expand-lg">
    @include('shared.loading-feedback')
    @php
        $navigation = [
            '' => [['admin.dashboard', 'grid-1x2', 'Dasbor']],
            'TOKO' => [
                ['admin.products.index', 'box-seam', 'Produk'],
                ['admin.categories.index', 'tags', 'Kategori'],
                ['admin.orders.index', 'receipt', 'Pesanan'],
                ['admin.shipping-rates.index', 'truck', 'Tarif pengiriman'],
            ],
            'PELANGGAN' => [
                ['admin.memberships.index', 'mortarboard', 'Anggota sekolah'],
                ['admin.tickets.index', 'chat-left-text', 'Komplain & retur'],
            ],
            'KONFIGURASI' => [['admin.settings.edit', 'gear', 'Pengaturan toko']],
        ];
    @endphp
    <a class="visually-hidden-focusable position-absolute z-3 p-2 bg-white" href="#main-content">Lewati ke konten</a>
    <div class="app-wrapper">
        <header class="app-header navbar navbar-expand bg-white">
            <div class="container-fluid gap-2">
                <button class="btn btn-light px-2" type="button" data-lte-toggle="sidebar" aria-controls="admin-sidebar" aria-label="Buka atau tutup menu">
                    <i class="bi bi-list fs-5" aria-hidden="true"></i>
                </button>
                <a class="btn btn-sm btn-outline-secondary ms-2 d-none d-md-inline-flex align-items-center gap-2" href="{{ url('/') }}">
                    <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Lihat toko
                </a>
                <div class="d-flex align-items-center gap-3 ms-auto">
                    <a class="btn btn-sm btn-light text-truncate" href="{{ route('security') }}" aria-label="Pengaturan keamanan akun">
                        <i class="bi bi-person-circle" aria-hidden="true"></i><span class="d-none d-sm-inline ms-1">{{ auth()->user()->name }}</span>
                    </a>
                    <form method="POST" action="{{ url('/logout') }}">
                        @csrf
                        <button class="btn btn-sm btn-outline-secondary" type="submit"><i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i> Keluar</button>
                    </form>
                </div>
            </div>
        </header>

        <aside class="app-sidebar" id="admin-sidebar" data-bs-theme="dark">
            <div class="sidebar-brand">
                <a href="{{ route('admin.dashboard') }}" class="admin-brand">
                    <img class="admin-brand-logo" src="{{ asset('images/brand/jsit-indonesia.webp') }}" width="46" height="44" alt="Logo JSIT Indonesia">
                    <span class="admin-brand-name">Koperasi JSIT<small>Panel administrasi</small></span>
                </a>
            </div>
            <div class="sidebar-wrapper">
                <nav class="px-2 pt-3" aria-label="Menu administrasi">
                    <ul class="nav sidebar-menu flex-column">
                        @foreach ($navigation as $group => $links)
                            @if ($group !== '')
                                <li class="nav-header">{{ $group }}</li>
                            @endif
                            @foreach ($links as [$routeName, $icon, $label])
                                @php
                                    $pattern = str_ends_with($routeName, '.index') ? substr($routeName, 0, -5).'*' : $routeName;
                                    $selected = request()->routeIs($pattern);
                                @endphp
                                <li class="nav-item">
                                    <a href="{{ route($routeName) }}" @class(['nav-link', 'active' => $selected]) @if($selected) aria-current="page" @endif>
                                        <i class="nav-icon bi bi-{{ $icon }}" aria-hidden="true"></i>
                                        <p class="mb-0">{{ $label }}</p>
                                    </a>
                                </li>
                            @endforeach
                        @endforeach
                    </ul>
                </nav>
            </div>
        </aside>

        <main class="app-main" id="main-content" tabindex="-1">
            <div class="admin-content">
                <div class="admin-heading d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                    <div>
                        @hasSection('back')
                            <div class="mb-2">@yield('back')</div>
                        @endif
                        <h1>@yield('title', 'Dasbor')</h1>
                        @hasSection('description')
                            <p>@yield('description')</p>
                        @endif
                    </div>
                    @yield('heading_actions')
                </div>

                @if (session('status'))
                    <div class="alert alert-success" role="status">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
