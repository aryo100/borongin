@php
    $imageUrl = match (true) {
        blank($path) => asset('images/brand/store-placeholder.webp'),
        str_starts_with($path, 'images/'), str_starts_with($path, 'storage/') => asset($path),
        str_starts_with($path, 'https://') => $path,
        default => \Illuminate\Support\Facades\Storage::disk('public')->url($path),
    };
@endphp
{{ $imageUrl }}
