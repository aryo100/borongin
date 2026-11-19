@if($paginator->hasPages())
    <nav class="pagination" aria-label="Halaman hasil">
        @if($paginator->previousPageUrl())<a class="button button-secondary button-small" href="{{ $paginator->previousPageUrl() }}" rel="prev">@include('store.partials.icon', ['name' => 'back']) Sebelumnya</a>@endif
        <span>Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>
        @if($paginator->nextPageUrl())<a class="button button-secondary button-small" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya @include('store.partials.icon', ['name' => 'arrow'])</a>@endif
    </nav>
@endif
