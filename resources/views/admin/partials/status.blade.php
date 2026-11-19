@php
    [$label, $style] = [
        'awaiting_payment' => ['Menunggu pembayaran', 'warning'],
        'payment_review' => ['Perlu verifikasi', 'warning'],
        'processing' => ['Diproses', 'info'],
        'shipped' => ['Dikirim', 'primary'],
        'completed' => ['Selesai', 'success'],
        'cancelled' => ['Dibatalkan', 'danger'],
        'expired' => ['Kedaluwarsa', 'secondary'],
        'pending' => ['Menunggu tinjauan', 'warning'],
        'approved' => ['Disetujui', 'success'],
        'rejected' => ['Ditolak', 'danger'],
        'open' => ['Terbuka', 'warning'],
        'answered' => ['Ditanggapi', 'info'],
        'closed' => ['Ditutup', 'secondary'],
        'active' => ['Aktif', 'success'],
        'inactive' => ['Nonaktif', 'secondary'],
    ][$value] ?? [$value, 'secondary'];
@endphp
@if ($plain ?? false)
    {{ $label }}
@else
    <span class="badge rounded-pill text-{{ $style }}-emphasis bg-{{ $style }}-subtle border border-{{ $style }}-subtle">{{ $label }}</span>
@endif
