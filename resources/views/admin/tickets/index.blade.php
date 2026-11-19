@extends('admin.layout')
@section('title', 'Komplain dan retur')

@section('content')
    <div class="card">
        <div class="card-header">
            <form class="admin-filter" style="--filter-selects: 2" method="GET" action="{{ route('admin.tickets.index') }}">
                <div><label for="q" class="form-label">Cari tiket</label><input class="form-control" id="q" name="q" type="search" value="{{ $query }}" placeholder="Subjek atau nomor pesanan"></div>
                <div>
                    <label for="kind" class="form-label">Jenis</label>
                    <select class="form-select" id="kind" name="kind">
                        <option value="">Semua jenis</option>
                        <option value="support" @selected($kind === 'support')>Komplain</option>
                        <option value="return" @selected($kind === 'return')>Retur</option>
                    </select>
                </div>
                <div>
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Semua status</option>
                        @foreach (['open', 'answered', 'closed'] as $option)
                            <option value="{{ $option }}" @selected($status === $option)>@include('admin.partials.status', ['value' => $option, 'plain' => true])</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-outline-primary" type="submit">Terapkan</button>
            </form>
        </div>
        @if ($tickets->isEmpty())
            <p class="admin-empty mb-0">Belum ada tiket yang cocok.</p>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th scope="col">Subjek</th><th scope="col">Jenis</th><th scope="col">Pesanan</th><th scope="col">Status</th><th scope="col">Dibuat</th><th scope="col" class="text-end">Tindakan</th></tr></thead>
                    <tbody>
                        @foreach ($tickets as $ticket)
                            <tr>
                                <th scope="row"><a href="{{ route('admin.tickets.show', $ticket) }}">{{ $ticket->subject }}</a></th>
                                <td>{{ $ticket->kind === 'return' ? 'Retur' : 'Komplain' }}</td>
                                <td class="text-nowrap"><a href="{{ route('admin.orders.show', $ticket->order) }}">{{ $ticket->order->public_id }}</a></td>
                                <td>@include('admin.partials.status', ['value' => $ticket->status])</td>
                                <td class="text-nowrap">{{ $ticket->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</td>
                                <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.tickets.show', $ticket) }}">Buka</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        @include('admin.partials.pagination', ['items' => $tickets])
    </div>
@endsection
