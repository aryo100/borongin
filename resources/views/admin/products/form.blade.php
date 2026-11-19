@extends('admin.layout')
@section('title', $product->exists ? 'Edit produk' : 'Tambah produk')
@section('back')
    <a href="{{ route('admin.products.index') }}" class="link-secondary"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Produk</a>
@endsection

@section('content')
    @php $hasVariantInput = old('variant_id') !== null; @endphp
    <section class="card">
        <div class="card-header"><h2 class="card-title">Informasi produk</h2></div>
        @if ($categories->isEmpty())
            <div class="card-body">
                <p>Buat kategori sebelum menambah produk.</p>
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">Tambah kategori</a>
            </div>
        @else
            <form class="card-body" method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data">
                @csrf
                @if ($product->exists) @method('PUT') @endif
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Nama produk</label>
                        <input class="form-control" id="name" name="name" value="{{ $hasVariantInput ? $product->name : old('name', $product->name) }}" maxlength="150" required>
                    </div>
                    <div class="col-md-6">
                        <label for="category-id" class="form-label">Kategori</label>
                        <select class="form-select" id="category-id" name="category_id" required>
                            <option value="">Pilih kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="slug" class="form-label">Slug</label>
                        <input class="form-control" id="slug" name="slug" value="{{ old('slug', $product->slug) }}" maxlength="150" aria-describedby="slug-help">
                        <div class="form-text" id="slug-help">Kosongkan untuk membuat slug dari nama produk.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="photos" class="form-label">Tambah foto produk</label>
                        <input class="form-control" type="file" id="photos" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple aria-describedby="photo-help" data-product-photos>
                        <div class="form-text" id="photo-help">JPG, PNG, atau WebP. Maksimal 8 foto, 5 MB per foto, dan 7 MB per unggahan.</div>
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="description" name="description" rows="5" maxlength="10000">{{ old('description', $product->description) }}</textarea>
                    </div>
                    <fieldset class="col-12">
                        <legend class="form-label fs-6">Tampilan di toko</legend>
                        @foreach (['active' => 'Aktif', 'members_only' => 'Khusus anggota sekolah', 'featured' => 'Tampilkan sebagai unggulan'] as $field => $label)
                            <div class="form-check form-check-inline">
                                <input type="hidden" name="{{ $field }}" value="0">
                                <input class="form-check-input" type="checkbox" name="{{ $field }}" id="product-{{ $field }}" value="1" @checked($hasVariantInput ? $product->{$field} : old($field, $product->{$field}))>
                                <label class="form-check-label" for="product-{{ $field }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    </fieldset>
                </div>
                <button class="btn btn-primary mt-4" type="submit">Simpan produk</button>
            </form>
        @endif
    </section>

    @if ($product->exists)
        @if ($product->images->isNotEmpty())
            <section class="card">
                <div class="card-header"><h2 class="card-title">Foto produk</h2></div>
                <div class="card-body row row-cols-2 row-cols-md-4 row-cols-xl-6 g-3">
                    @foreach ($product->images as $image)
                        <div class="col">
                            <img class="admin-photo mb-2" src="@include('store.partials.image-url', ['path' => $image->path])" alt="{{ $image->alt ?: $product->name }}" loading="lazy">
                            <form method="POST" action="{{ route('admin.products.images.destroy', [$product, $image->id]) }}" data-confirm="Hapus foto ini?">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger w-100" type="submit">Hapus foto</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="card">
            <div class="card-header">
                <h2 class="card-title">Varian dan stok</h2>
                <p class="mb-0 mt-1 text-secondary small">Stok mencakup unit yang tersedia dan jumlah yang sedang dipesan.</p>
            </div>
            <div class="card-body">
                @foreach ($product->variants as $variant)
                    <details class="border rounded-2 mb-3" @if((string) old('variant_id') === (string) $variant->id) open @endif>
                        <summary class="p-3">
                            <strong>{{ $variant->name }}</strong>
                            <span class="small text-secondary ms-2">{{ $variant->sku }} · {{ $variant->preorder ? 'Preorder' : $variant->available.' tersedia' }}</span>
                            <span class="ms-2">@include('admin.partials.status', ['value' => $variant->active ? 'active' : 'inactive'])</span>
                        </summary>
                        <div class="p-3 pt-0">
                            @include('admin.products.variant-form', ['variant' => $variant])
                            <form class="mt-2" method="POST" action="{{ route('admin.products.variants.destroy', [$product, $variant->id]) }}" data-confirm="Hapus varian ini? Varian dengan riwayat pesanan akan dinonaktifkan.">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit">Hapus varian</button>
                            </form>
                        </div>
                    </details>
                @endforeach
                <details class="border rounded-2" @if($product->variants->isEmpty() || old('variant_id') === 'new') open @endif>
                    <summary class="p-3 fw-semibold"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i> Tambah varian</summary>
                    <div class="p-3 pt-0">@include('admin.products.variant-form', ['variant' => null])</div>
                </details>
            </div>
        </section>

        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" data-confirm="Hapus produk ini? Produk dengan riwayat pesanan akan dinonaktifkan.">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger" type="submit">Hapus produk</button>
        </form>
    @endif
@endsection
