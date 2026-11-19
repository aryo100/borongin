@if ($orders->isEmpty())
    <p class="admin-empty mb-0">Belum ada pesanan yang cocok.</p>
@else
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th scope="col">Pesanan</th><th scope="col">Pelanggan</th><th scope="col">Tanggal</th><th scope="col">Status</th><th scope="col" class="text-end">Total</th></tr></thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <th scope="row">
                            <a class="fw-semibold link-primary text-decoration-none" href="{{ route('admin.orders.show', $order) }}">{{ $order->public_id }}</a>
                        </th>
                        <td>{{ $order->name }}</td>
                        <td class="text-nowrap">{{ $order->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</td>
                        <td>@include('admin.partials.status', ['value' => $order->status])</td>
                        <td class="text-nowrap text-end">Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
