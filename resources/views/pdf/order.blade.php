<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 44px; }
        body { color: #172521; font: 11px DejaVu Sans, sans-serif; }
        h1 { color: #07654b; margin: 0 0 24px; }
        h2 { font-size: 14px; margin: 24px 0 8px; }
        p { line-height: 1.5; margin: 3px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #cdd8d3; padding: 8px 5px; text-align: left; vertical-align: top; }
        th { background: #ecf5f0; }
        .number { text-align: right; white-space: nowrap; }
        small { color: #426157; }
        div.totals { margin-top: 16px; text-align: right; }
    </style>
</head>
<body>
    <h1>Invoice Koperasi JSIT</h1>
    <p><strong>Nomor pesanan</strong> {{ $order->public_id }}</p>
    <p><strong>Tanggal</strong> {{ $order->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>

    <h2>Pelanggan</h2>
    <p>{{ $order->name }}<br>{{ $order->email }}<br>{{ $order->phone }}</p>

    <h2>Pengiriman</h2>
    <p>{{ $order->shipping_name }}</p>
    @if ($order->address)
        <p>{{ $order->address['address_line'] }}<br>{{ $order->address['city'] }}, {{ $order->address['province'] }} {{ $order->address['postal_code'] }}</p>
    @endif
    @if ($order->eta_days)
        <p>Estimasi penyiapan prapesan {{ $order->eta_days }} hari.</p>
    @endif

    <h2>Rincian pesanan</h2>
    <table>
        <thead>
            <tr><th>Produk</th><th>SKU</th><th class="number">Jumlah</th><th class="number">Harga satuan</th><th class="number">Subtotal</th></tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}<br><small>{{ $item->variant_name }}</small></td>
                    <td>{{ $item->sku }}</td>
                    <td class="number">{{ $item->quantity }}</td>
                    <td class="number">Rp{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="number">Rp{{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="totals">
        <p>Subtotal Rp{{ number_format($order->subtotal, 0, ',', '.') }}</p>
        <p>Pengiriman Rp{{ number_format($order->shipping_fee, 0, ',', '.') }}</p>
        <p><strong>Total Rp{{ number_format($order->total, 0, ',', '.') }}</strong></p>
    </div>
</body>
</html>
