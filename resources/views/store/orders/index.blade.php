@extends('store.layout')
@section('title', 'Pesanan Saya')

@section('content')
    @include('store.partials.page-intro', ['heading' => 'Pesanan Saya', 'trail' => ['Pesanan Saya' => null]])
    <div class="shell page-body">
        @include('store.partials.account-nav')
        @if($orders->isNotEmpty())
            <section class="order-list" aria-label="Riwayat pesanan">
                @foreach($orders as $order)
                    <article class="panel order-list-item">
                        <div><h2>{{ $order->public_id }}</h2><p>{{ $order->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j F Y') }}</p></div>
                        @include('store.partials.order-status', ['status' => $order->status])
                        <strong>Rp{{ number_format($order->total, 0, ',', '.') }}</strong>
                        <a href="{{ route('store.orders.show', $order->public_id) }}" class="button button-secondary button-small">Detail Pesanan @include('store.partials.icon', ['name' => 'arrow'])</a>
                    </article>
                @endforeach
            </section>
            @include('store.partials.pagination', ['paginator' => $orders])
        @else
            <div class="empty-state"><span class="empty-icon">@include('store.partials.icon', ['name' => 'receipt'])</span><h2>Belum ada pesanan</h2><p>Pesanan dari akun Anda akan muncul di sini.</p><a href="{{ route('store.catalog') }}" class="button button-primary">Jelajahi Katalog</a></div>
        @endif
    </div>
@endsection
