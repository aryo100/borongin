@php
    $orderLabels = ['awaiting_payment' => 'Menunggu Pembayaran', 'payment_review' => 'Verifikasi Pembayaran', 'processing' => 'Sedang Disiapkan', 'shipped' => 'Dikirim', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan', 'expired' => 'Kedaluwarsa'];
    $tone = match ($status) {
        'completed', 'shipped' => ' status-success',
        'cancelled', 'expired' => ' status-closed',
        'awaiting_payment', 'payment_review' => ' status-pending',
        default => '',
    };
@endphp
<span class="status-pill{{ $tone }}"><span aria-hidden="true"></span>{{ $orderLabels[$status] ?? ucfirst($status) }}</span>
