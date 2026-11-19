@extends('admin.layout')
@section('title', 'Produk')
@section('heading_actions')
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i> Tambah produk</a>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <form class="admin-filter" style="--filter-selects: 3" method="GET" action="{{ route('admin.products.index') }}">
                <div><label for="q" class="form-label">Cari produk</label><input class="form-control" id="q" name="q" type="search" value="{{ $query }}" placeholder="Nama atau SKU"></div>
                <div>
                    <label for="category" class="form-label">Kategori</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">Semua kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected($categoryId === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="visibility" class="form-label">Status</label>
                    <select class="form-select" id="visibility" name="visibility">
                        <option value="">Semua status</option>
                        <option value="active" @selected($visibility === 'active')>Aktif</option>
                        <option value="inactive" @selected($visibility === 'inactive')>Nonaktif</option>
                    </select>
                </div>
                <div>
                    <label for="stock" class="form-label">Stok</label>
                    <select class="form-select" id="stock" name="stock">
                        <option value="">Semua stok</option>
                        <option value="low" @selected($stock === 'low')>Hampir habis</option>
                    </select>
                </div>
                <button class="btn btn-outline-primary" type="submit">Terapkan</button>
            </form>
        </div>
        @if ($products->isEmpty())
            <p class="admin-empty mb-0">Belum ada produk yang cocok.</p>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th scope="col">Produk</th><th scope="col">Kategori</th><th scope="col">Varian</th><th scope="col">Status</th><th scope="col" class="text-end">Tindakan</th></tr></thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <th scope="row">
                                    <div class="d-flex align-items-center gap-3">
                                        @if ($product->images->first())
                                            <img class="admin-thumb flex-shrink-0" src="@include('store.partials.image-url', ['path' => $product->images->first()->path])" alt="" loading="lazy">
                                        @else
                                            <span class="admin-thumb d-grid flex-shrink-0 align-items-center text-center text-secondary"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
                                        @endif
                                        <div>
                                            <a class="link-primary text-decoration-none fw-semibold" href="{{ route('admin.products.edit', $product) }}">{{ $product->name }}</a>
                                            @if ($product->members_only || $product->featured)
                                                <small class="d-block fw-normal text-secondary">{{ implode(' · ', array_filter([$product->members_only ? 'Khusus anggota' : null, $product->featured ? 'Unggulan' : null])) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </th>
                                <td>{{ $product->category?->name ?? '—' }}</td>
                                <td>{{ $product->variants_count }}</td>
                                <td>@include('admin.partials.status', ['value' => $product->active ? 'active' : 'inactive'])</td>
                                <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.products.edit', $product) }}">Edit</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        @include('admin.partials.pagination', ['items' => $products])
    </div>
@endsection
