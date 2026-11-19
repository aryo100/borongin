@extends('admin.layout')
@section('title', $category->exists ? 'Edit kategori' : 'Tambah kategori')
@section('back')
    <a href="{{ route('admin.categories.index') }}" class="link-secondary"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Kategori</a>
@endsection

@section('content')
    <div class="card">
        <form class="card-body" method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" enctype="multipart/form-data">
            @csrf
            @if ($category->exists) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Nama kategori</label>
                    <input class="form-control" id="name" name="name" value="{{ old('name', $category->name) }}" maxlength="150" required>
                </div>
                <div class="col-md-6">
                    <label for="slug" class="form-label">Slug</label>
                    <input class="form-control" id="slug" name="slug" value="{{ old('slug', $category->slug) }}" maxlength="150" aria-describedby="slug-help">
                    <div class="form-text" id="slug-help">Kosongkan untuk membuat slug dari nama kategori.</div>
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea class="form-control" id="description" name="description" rows="4" maxlength="3000">{{ old('description', $category->description) }}</textarea>
                </div>
                <div class="col-md-8">
                    <label for="image" class="form-label">Gambar kategori</label>
                    <input class="form-control" type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                    <div class="form-text">JPG, PNG, atau WebP hingga 5 MB.</div>
                    @if ($category->image_path)
                        <img class="admin-thumb mt-3" src="@include('store.partials.image-url', ['path' => $category->image_path])" alt="{{ $category->name }}">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="remove_image" id="remove-image" value="1" @checked(old('remove_image'))>
                            <label class="form-check-label" for="remove-image">Hapus gambar saat ini</label>
                        </div>
                    @endif
                </div>
            </div>
            <button class="btn btn-primary mt-4" type="submit">Simpan kategori</button>
        </form>
    </div>
    @if ($category->exists)
        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" data-confirm="Hapus kategori ini?">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger" type="submit">Hapus kategori</button>
        </form>
    @endif
@endsection
