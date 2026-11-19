@extends('admin.layout')
@section('title', $rate->exists ? 'Edit tarif' : 'Tambah tarif')
@section('back')
    <a href="{{ route('admin.shipping-rates.index') }}" class="link-secondary"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Tarif pengiriman</a>
@endsection

@section('content')
    <div class="card">
        <form class="card-body" action="{{ $rate->exists ? route('admin.shipping-rates.update', $rate) : route('admin.shipping-rates.store') }}" method="POST">
            @csrf
            @if ($rate->exists) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="name">Nama layanan</label>
                    <input class="form-control" id="name" name="name" value="{{ old('name', $rate->name) }}" maxlength="120" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="region">Provinsi</label>
                    <input class="form-control" id="region" name="region" value="{{ old('region', $rate->region) }}" maxlength="120" aria-describedby="region-help">
                    <div class="form-text" id="region-help">Wajib untuk pengiriman. Gunakan nama provinsi tujuan yang tampil di checkout.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="price">Tarif per pesanan (Rp)</label>
                    <input class="form-control" id="price" name="price" value="{{ old('price', $rate->price) }}" type="number" min="0" max="100000000" aria-describedby="price-help">
                    <div class="form-text" id="price-help">Tarif ambil sendiri selalu Rp0.</div>
                </div>
                <fieldset class="col-md-6">
                    <legend class="form-label fs-6">Opsi layanan</legend>
                    @foreach (['pickup' => 'Ambil sendiri', 'active' => 'Tampilkan di checkout'] as $field => $label)
                        <div class="form-check">
                            <input type="hidden" name="{{ $field }}" value="0">
                            <input class="form-check-input" type="checkbox" id="{{ $field }}" name="{{ $field }}" value="1" @checked(old($field, $rate->{$field}))>
                            <label class="form-check-label" for="{{ $field }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </fieldset>
            </div>
            <button class="btn btn-primary mt-4" type="submit">Simpan tarif</button>
        </form>
    </div>
    @if ($rate->exists)
        <form method="POST" action="{{ route('admin.shipping-rates.destroy', $rate) }}" data-confirm="Hapus tarif pengiriman ini?">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger" type="submit">Hapus tarif</button>
        </form>
    @endif
@endsection
