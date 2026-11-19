@extends('admin.layout')
@section('title', 'Detail pesanan')
@section('description', $order->public_id)
@section('back')
    <a href="{{ route('admin.orders.index') }}" class="link-secondary"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Semua pesanan</a>
@endsection
@section('heading_actions')
    <div class="fs-6">@include('admin.partials.status', ['value' => $order->status])</div>
@endsection

@section('content')
    <div class="row g-3 align-items-start">
        <div class="col-lg-8">
            <section class="card">
                <div class="card-header"><h2 class="card-title">Rincian produk</h2></div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th scope="col">Produk</th><th scope="col">Jumlah</th><th scope="col" class="text-end">Harga satuan</th><th scope="col" class="text-end">Subtotal</th></tr></thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <th scope="row">
                                        {{ $item->product_name }}
                                        <small class="d-block fw-normal text-secondary">{{ $item->variant_name }} · {{ $item->sku }}</small>
                                        @if ($item->lead_days > 0)<small class="d-block fw-normal text-secondary">Preorder {{ $item->lead_days }} hari</small>@endif
                                    </th>
                                    <td>{{ $item->quantity }}</td>
                                    <td class="text-nowrap text-end">Rp{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                    <td class="text-nowrap text-end">Rp{{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-body border-top">
                    <dl class="row mb-0 text-end">
                        <dt class="col-7 fw-normal">Subtotal</dt><dd class="col-5">Rp{{ number_format($order->subtotal, 0, ',', '.') }}</dd>
                        <dt class="col-7 fw-normal">Ongkos pengiriman</dt><dd class="col-5">Rp{{ number_format($order->shipping_fee, 0, ',', '.') }}</dd>
                        <dt class="col-7">Total</dt><dd class="col-5 fw-bold mb-0">Rp{{ number_format($order->total, 0, ',', '.') }}</dd>
                    </dl>
                </div>
            </section>

            <section class="card">
                <div class="card-header"><h2 class="card-title">Bukti pembayaran</h2></div>
                @forelse ($order->payments->sortByDesc('created_at') as $payment)
                    <div class="card-body @unless($loop->first) border-top @endunless">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                            <div>
                                <p class="mb-1">@include('admin.partials.status', ['value' => $payment->status])</p>
                                <p class="text-secondary small mb-0">Diunggah {{ $payment->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</p>
                            </div>
                            <a href="{{ route('admin.payments.proof', $payment) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-box-arrow-up-right me-1" aria-hidden="true"></i> Buka bukti (tab baru)
                            </a>
                        </div>
                        @if ($payment->reviewed_at)
                            <p class="text-secondary small mb-0 mt-2">Ditinjau {{ $payment->reviewed_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB{{ $payment->reviewer ? ' oleh '.$payment->reviewer->name : '' }}</p>
                        @endif
                        @if ($payment->note)
                            <p class="admin-break-lines mb-0 mt-2">{{ $payment->note }}</p>
                        @endif
                        @if ($payment->status === 'pending' && $order->status === 'payment_review')
                            <form method="POST" action="{{ route('admin.payments.update', $payment) }}" class="border-top pt-3 mt-3">
                                @csrf @method('PATCH')
                                <label class="form-label" for="payment-note-{{ $payment->id }}">Catatan verifikasi</label>
                                <textarea class="form-control" id="payment-note-{{ $payment->id }}" name="note" maxlength="1000" rows="2" aria-describedby="payment-help-{{ $payment->id }}">{{ old('note') }}</textarea>
                                <div class="form-text" id="payment-help-{{ $payment->id }}">Tulis alasan jika bukti ditolak agar pelanggan dapat mengunggah ulang.</div>
                                <div class="mt-3 d-flex flex-wrap gap-2">
                                    <button class="btn btn-success" name="action" value="approve" type="submit" data-confirm="Setujui pembayaran untuk pesanan ini?">Setujui pembayaran</button>
                                    <button class="btn btn-outline-danger" name="action" value="reject" type="submit">Tolak bukti</button>
                                </div>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="admin-empty mb-0">Belum ada bukti pembayaran.</p>
                @endforelse
            </section>

            @if ($order->tickets->isNotEmpty())
                <section class="card">
                    <div class="card-header"><h2 class="card-title">Komplain dan retur</h2></div>
                    <ul class="list-group list-group-flush">
                        @foreach ($order->tickets as $ticket)
                            <li class="list-group-item d-flex align-items-center justify-content-between gap-3">
                                <a href="{{ route('admin.tickets.show', $ticket) }}">{{ $ticket->subject }}</a>
                                @include('admin.partials.status', ['value' => $ticket->status])
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        <div class="col-lg-4">
            <section class="card">
                <div class="card-header"><h2 class="card-title">Pelanggan dan pengiriman</h2></div>
                <dl class="card-body mb-0">
                    <dt>Pelanggan</dt><dd class="admin-break-lines">{{ $order->name }}<br>{{ $order->email }}<br>{{ $order->phone }}</dd>
                    <dt>Pengiriman</dt>
                    <dd class="admin-break-lines">
                        {{ $order->shipping_name }}
                        @if ($order->address)
                            <br>{{ data_get($order->address, 'address_line') }}
                            <br>{{ data_get($order->address, 'city') }}, {{ data_get($order->address, 'province') }} {{ data_get($order->address, 'postal_code') }}
                        @endif
                    </dd>
                    @if ($order->tracking_number)
                        <dt>Nomor resi</dt><dd class="admin-break-lines">{{ $order->tracking_number }}</dd>
                    @endif
                    @if ($order->eta_days > 0)
                        <dt>Estimasi persiapan</dt><dd>{{ $order->eta_days }} hari</dd>
                    @endif
                    <dt>Dibuat</dt><dd>{{ $order->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</dd>
                    @if ($order->status === 'awaiting_payment')
                        <dt>Batas pembayaran</dt><dd class="mb-0">{{ $order->payment_due_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</dd>
                    @endif
                </dl>
            </section>

            @if (in_array($order->status, ['awaiting_payment', 'payment_review', 'processing', 'shipped'], true))
                <section class="card">
                    <div class="card-header"><h2 class="card-title">Tindakan pesanan</h2></div>
                    <div class="card-body">
                        @if ($order->status === 'processing' && $order->address !== null)
                            <form method="POST" action="{{ route('admin.orders.update', $order) }}" data-confirm="Tandai pesanan ini sudah dikirim?">
                                @csrf @method('PATCH')
                                <label class="form-label" for="tracking-number">Nomor resi</label>
                                <input class="form-control" id="tracking-number" name="tracking_number" maxlength="100" value="{{ old('tracking_number') }}" required>
                                <button class="btn btn-primary mt-3" type="submit" name="action" value="ship">Tandai sudah dikirim</button>
                            </form>
                        @elseif ($order->status === 'processing' || $order->status === 'shipped')
                            <form method="POST" action="{{ route('admin.orders.update', $order) }}" data-confirm="Selesaikan pesanan ini?">
                                @csrf @method('PATCH')
                                <button class="btn btn-primary" type="submit" name="action" value="complete">{{ $order->status === 'processing' ? 'Tandai sudah diambil' : 'Selesaikan pesanan' }}</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.orders.update', $order) }}" data-confirm="Batalkan pesanan ini?">
                                @csrf @method('PATCH')
                                <button class="btn btn-outline-danger" type="submit" name="action" value="cancel">Batalkan pesanan</button>
                            </form>
                        @endif
                    </div>
                </section>
            @endif
        </div>
    </div>
@endsection
