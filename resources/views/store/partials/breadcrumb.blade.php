<nav class="breadcrumb" aria-label="Jejak navigasi">
    <a href="{{ route('store.home') }}">Beranda</a>
    @foreach($trail as $label => $href)
        @include('store.partials.icon', ['name' => 'chevron'])
        @if($href)<a href="{{ $href }}">{{ $label }}</a>@else<span aria-current="page">{{ $label }}</span>@endif
    @endforeach
</nav>
