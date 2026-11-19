@extends('admin.layout')
@section('title', 'Pesanan')

@section('content')
    <div class="card">
        <div class="card-header">
            <form class="admin-filter" style="--filter-selects: 2" method="GET" action="{{ route('admin.orders.index') }}">
                <div><label for="q" class="form-label">Cari pesanan</label><input class="form-control" id="q" name="q" type="search" value="{{ $query }}" placeholder="Nomor pesanan, pelanggan, email, atau resi"></div>
                <div>
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Semua status</option>
                        @foreach ($statuses as $option)
                            <option value="{{ $option }}" @selected($status === $option)>@include('admin.partials.status', ['value' => $option, 'plain' => true])</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="date" class="form-label">Tanggal</label>
                    <select class="form-select" id="date" name="date">
                        <option value="">Semua tanggal</option>
                        <option value="today" @selected($date === 'today')>Hari ini</option>
                    </select>
                </div>
                <button class="btn btn-outline-primary" type="submit">Terapkan</button>
            </form>
        </div>
        @include('admin.partials.order-table')
        @include('admin.partials.pagination', ['items' => $orders])
    </div>
@endsection
