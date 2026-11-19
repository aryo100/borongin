@extends('admin.layout')
@section('title', 'Dasbor')
@section('description', 'Ringkasan pesanan, pembayaran, dan antrean toko.')

@section('heading_actions')
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i> Tambah produk</a>
@endsection

@section('content')
    @php
        $metrics = [
            ['Perlu verifikasi', $counts['payments'], 'credit-card', 'warning', route('admin.orders.index', ['status' => 'payment_review'])],
            ['Pesanan diproses', $counts['processing'], 'box-seam', 'primary', route('admin.orders.index', ['status' => 'processing'])],
            ['Pesanan hari ini', $counts['ordersToday'], 'receipt', 'success', route('admin.orders.index', ['date' => 'today'])],
            ['Varian hampir habis', $counts['stock'], 'exclamation-triangle', 'danger', route('admin.products.index', ['stock' => 'low'])],
        ];
        $queues = [
            ['Verifikasi pembayaran', $counts['payments'], 'credit-card', route('admin.orders.index', ['status' => 'payment_review'])],
            ['Persiapan pengiriman', $counts['processing'], 'truck', route('admin.orders.index', ['status' => 'processing'])],
            ['Pengajuan anggota', $counts['memberships'], 'mortarboard', route('admin.memberships.index')],
            ['Komplain atau retur terbuka', $counts['tickets'], 'chat-left-text', route('admin.tickets.index', ['status' => 'open'])],
        ];
    @endphp
    <section class="card mb-4" aria-labelledby="sales-title">
        <div class="card-body">
            <h2 id="sales-title" class="admin-metric-label">Penjualan produk terverifikasi</h2>
            <p class="admin-metric-number mb-2">Rp{{ number_format($counts['sales'], 0, ',', '.') }}</p>
            <p class="text-secondary small mb-0">Total nilai produk dari pesanan diproses, dikirim, dan selesai. Ongkos kirim dicatat pada setiap pesanan.</p>
        </div>
    </section>
    <div class="row row-cols-2 row-cols-xl-4 g-3 mb-4">
        @foreach ($metrics as [$label, $number, $icon, $style, $url])
            <div class="col">
                <a class="card admin-metric" href="{{ $url }}">
                    <div class="card-body d-flex align-items-start justify-content-between gap-2">
                        <div><span class="admin-metric-label">{{ $label }}</span><span class="admin-metric-number">{{ number_format($number, 0, ',', '.') }}</span></div>
                        <span class="admin-metric-icon bg-{{ $style }}-subtle text-{{ $style }}-emphasis d-none d-sm-grid"><i class="bi bi-{{ $icon }}" aria-hidden="true"></i></span>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="row g-3 align-items-start">
        <div class="col-xl-8">
            <section class="card mb-0">
                <div class="card-header d-flex align-items-center justify-content-between gap-2">
                    <h2 class="card-title">Pesanan terbaru</h2>
                    <a class="link-primary text-nowrap" href="{{ route('admin.orders.index') }}">Semua pesanan</a>
                </div>
                @include('admin.partials.order-table')
            </section>
        </div>
        <div class="col-xl-4">
            <section class="card mb-0">
                <div class="card-header"><h2 class="card-title">Perlu ditangani</h2></div>
                <div class="list-group list-group-flush rounded-bottom">
                    @foreach ($queues as [$label, $count, $icon, $url])
                        <a href="{{ $url }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-2 py-3">
                            <span><i class="bi bi-{{ $icon }} me-2 text-secondary" aria-hidden="true"></i>{{ $label }}</span>
                            <span class="badge rounded-pill {{ $count > 0 ? 'text-bg-primary' : 'text-secondary-emphasis bg-secondary-subtle' }}">{{ $count }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
@endsection
