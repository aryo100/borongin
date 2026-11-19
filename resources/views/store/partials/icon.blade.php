<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false" {{ $attributes ?? '' }}>
    @switch($name)
        @case('search') <circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.5 4.5"/> @break
        @case('user') <circle cx="12" cy="7.7" r="3.4"/><path d="M4 20v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/> @break
        @case('bag') <path d="M5 8h14l1.4 13H3.6L5 8Z"/><path d="M9 10V6a3 3 0 0 1 6 0v4"/> @break
        @case('arrow') <path d="M4 12h15m-6-6 6 6-6 6"/> @break
        @case('back') <path d="M20 12H5m6-6-6 6 6 6"/> @break
        @case('chevron') <path d="m9 6 6 6-6 6"/> @break
        @case('down') <path d="m6 9 6 6 6-6"/> @break
        @case('truck') <path d="M3 5h12v12H3zM15 9h3l3 4v4h-6"/><circle cx="7" cy="18" r="2" fill="var(--icon-bg, white)"/><circle cx="17.5" cy="18" r="2" fill="var(--icon-bg, white)"/> @break
        @case('shield') <path d="M12 22c5-2.6 8-5.6 8-11V5l-8-3-8 3v6c0 5.4 3 8.4 8 11Z"/><path d="m8 12 2.5 2.5 5-5"/> @break
        @case('pin') <path d="M19 10c0 5-7 11-7 11s-7-6-7-11a7 7 0 1 1 14 0Z"/><circle cx="12" cy="10" r="2.3"/> @break
        @case('clock') <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/> @break
        @case('menu') <path d="M4 6h16M4 12h16M4 18h16"/> @break
        @case('close') <path d="m6 6 12 12M6 18 18 6"/> @break
        @case('plus') <path d="M12 5v14M5 12h14"/> @break
        @case('minus') <path d="M5 12h14"/> @break
        @case('check') <path d="m5 12 4 4L19 6"/> @break
        @case('mail') <rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 6 8 7 8-7"/> @break
        @case('phone') <path d="M5 4h4l2 5-3 2c1.4 2.7 2.3 3.6 5 5l2-3 5 2v4a1 1 0 0 1-1 1C10 20 4 14 4 5a1 1 0 0 1 1-1Z"/> @break
        @case('receipt') <path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Zm3 5h6m-6 4h6"/> @break
        @case('heart') <path d="M12 20 4.9 13a4.5 4.5 0 0 1 6.4-6.4l.7.7.7-.7a4.5 4.5 0 0 1 6.4 6.4L12 20Z"/> @break
        @case('card') <rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h3"/> @break
        @case('upload') <path d="M12 16V3m-5 5 5-5 5 5M4 16v5h16v-5"/> @break
        @case('filter') <path d="M3 6h18M6 12h12m-9 6h6"/> @break
        @case('spark') <path d="M12 2c0 6-4 10-10 10 6 0 10 4 10 10 0-6 4-10 10-10-6 0-10-4-10-10Z"/> @break
    @endswitch
</svg>
