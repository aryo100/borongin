@extends('admin.layout')
@section('title', 'Kategori')
@section('heading_actions')
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i> Tambah kategori</a>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <form class="admin-filter" method="GET" action="{{ route('admin.categories.index') }}">
                <div><label for="q" class="form-label">Cari kategori</label><input class="form-control" id="q" name="q" type="search" value="{{ $query }}" placeholder="Nama kategori"></div>
                <button class="btn btn-outline-primary" type="submit">Cari</button>
            </form>
        </div>
        @if ($categories->isEmpty())
            <p class="admin-empty mb-0">Belum ada kategori yang cocok.</p>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th scope="col">Kategori</th><th scope="col">Slug</th><th scope="col">Produk</th><th scope="col" class="text-end">Tindakan</th></tr></thead>
                    <tbody>
                        @foreach ($categories as $category)
                            <tr>
                                <th scope="row">
                                    <div class="d-flex align-items-center gap-2">
                                        @if ($category->image_path)
                                            <img class="admin-thumb" src="@include('store.partials.image-url', ['path' => $category->image_path])" alt="" loading="lazy">
                                        @endif
                                        {{ $category->name }}
                                    </div>
                                </th>
                                <td>{{ $category->slug }}</td>
                                <td>{{ $category->products_count }}</td>
                                <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.categories.edit', $category) }}">Edit</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        @include('admin.partials.pagination', ['items' => $categories])
    </div>
@endsection
