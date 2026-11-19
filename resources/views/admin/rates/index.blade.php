@extends('admin.layout')
@section('title', 'Tarif pengiriman')
@section('heading_actions')
    <a href="{{ route('admin.shipping-rates.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i> Tambah tarif</a>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <form class="admin-filter" method="GET" action="{{ route('admin.shipping-rates.index') }}">
                <div><label for="q" class="form-label">Cari tarif</label><input class="form-control" id="q" name="q" type="search" value="{{ $query }}" placeholder="Nama layanan atau provinsi"></div>
                <button class="btn btn-outline-primary" type="submit">Cari</button>
            </form>
        </div>
        @if ($rates->isEmpty())
            <p class="admin-empty mb-0">Belum ada tarif yang cocok.</p>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th scope="col">Layanan</th><th scope="col">Provinsi</th><th scope="col">Tarif</th><th scope="col">Status</th><th scope="col" class="text-end">Tindakan</th></tr></thead>
                    <tbody>
                        @foreach ($rates as $rate)
                            <tr>
                                <th scope="row">{{ $rate->name }}</th>
                                <td>{{ $rate->pickup ? 'Ambil sendiri' : $rate->region }}</td>
                                <td>Rp{{ number_format($rate->price, 0, ',', '.') }}</td>
                                <td>@include('admin.partials.status', ['value' => $rate->active ? 'active' : 'inactive'])</td>
                                <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.shipping-rates.edit', $rate) }}">Edit</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        @include('admin.partials.pagination', ['items' => $rates])
    </div>
@endsection
