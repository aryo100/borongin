<div class="page-intro">
    <div class="shell">
        @include('store.partials.breadcrumb')
        <h1>{{ $heading }}</h1>
        @if(filled($description ?? null))<p>{{ $description }}</p>@endif
    </div>
</div>
