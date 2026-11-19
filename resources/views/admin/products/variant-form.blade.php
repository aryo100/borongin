@php
    $variantId = (string) ($variant?->id ?? 'new');
    $inputValue = fn ($field, $default = '') => (string) old('variant_id') === $variantId ? old($field, $default) : $default;
@endphp
<form method="POST" action="{{ $variant ? route('admin.products.variants.update', [$product, $variant->id]) : route('admin.products.variants.store', $product) }}">
    @csrf
    @if ($variant) @method('PUT') @endif
    <input type="hidden" name="variant_id" value="{{ $variantId }}">
    <div class="row g-3">
        <div class="col-sm-6">
            <label class="form-label" for="variant-{{ $variantId }}-name">Nama varian</label>
            <input class="form-control" id="variant-{{ $variantId }}-name" name="name" maxlength="150" value="{{ $inputValue('name', $variant?->name) }}" required>
        </div>
        <div class="col-sm-6">
            <label class="form-label" for="variant-{{ $variantId }}-sku">SKU</label>
            <input class="form-control" id="variant-{{ $variantId }}-sku" name="sku" maxlength="80" value="{{ $inputValue('sku', $variant?->sku) }}" required>
        </div>
        <div class="col-sm-4">
            <label class="form-label" for="variant-{{ $variantId }}-price">Harga (Rp)</label>
            <input class="form-control" id="variant-{{ $variantId }}-price" name="price" type="number" min="1" max="1000000000" value="{{ $inputValue('price', $variant?->price) }}" required>
        </div>
        <div class="col-sm-4">
            <label class="form-label" for="variant-{{ $variantId }}-stock">Stok total</label>
            <input class="form-control" id="variant-{{ $variantId }}-stock" name="stock" type="number" min="{{ $variant?->reserved ?? 0 }}" max="1000000" value="{{ $inputValue('stock', $variant?->stock ?? 0) }}" required>
            @if ($variant && $variant->reserved > 0)
                <div class="form-text">{{ $variant->reserved }} sedang dipesan.</div>
            @endif
        </div>
        <div class="col-sm-4">
            <label class="form-label" for="variant-{{ $variantId }}-lead">Waktu preorder (hari)</label>
            <input class="form-control" id="variant-{{ $variantId }}-lead" name="lead_days" type="number" min="0" max="365" value="{{ $inputValue('lead_days', $variant?->lead_days ?? 0) }}" required>
        </div>
        <div class="col-12">
            @foreach (['active' => ['Aktif', $variant?->active ?? true], 'preorder' => ['Preorder', $variant?->preorder ?? false]] as $field => [$label, $default])
                <div class="form-check form-check-inline">
                    <input type="hidden" name="{{ $field }}" value="0">
                    <input class="form-check-input" id="variant-{{ $variantId }}-{{ $field }}" type="checkbox" name="{{ $field }}" value="1" @checked($inputValue($field, $default))>
                    <label class="form-check-label" for="variant-{{ $variantId }}-{{ $field }}">{{ $label }}</label>
                </div>
            @endforeach
        </div>
    </div>
    <button class="btn btn-sm btn-primary mt-3" type="submit">{{ $variant ? 'Simpan varian' : 'Tambah varian' }}</button>
</form>
