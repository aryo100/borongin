@if ($items->hasPages())
    <div class="card-footer bg-white pt-3 pb-2">
        {{ $items->links('pagination::bootstrap-5') }}
    </div>
@endif
