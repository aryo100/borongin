@extends('admin.layout')
@section('title', 'Detail tiket')
@section('back')
    <a href="{{ route('admin.tickets.index') }}" class="link-secondary"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Komplain dan retur</a>
@endsection
@section('heading_actions')
    <div class="fs-6">@include('admin.partials.status', ['value' => $ticket->status])</div>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-lg-6">
            <article class="card">
                <div class="card-header"><h2 class="card-title">{{ $ticket->subject }}</h2></div>
                <div class="card-body">
                    <p class="text-secondary">{{ $ticket->kind === 'return' ? 'Retur' : 'Komplain' }} · {{ $ticket->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</p>
                    <p class="admin-break-lines">{{ $ticket->message }}</p>
                    <dl class="mb-0">
                        <dt>Pesanan</dt>
                        <dd><a class="admin-break-lines" href="{{ route('admin.orders.show', $ticket->order) }}">{{ $ticket->order->public_id }}</a></dd>
                        <dt>Pelanggan</dt><dd class="mb-0 admin-break-lines">{{ $ticket->order->name }}<br>{{ $ticket->order->email }}</dd>
                    </dl>
                </div>
            </article>
        </div>
        <div class="col-lg-6">
            <section class="card">
                <div class="card-header"><h2 class="card-title">Tanggapan</h2></div>
                <form class="card-body" method="POST" action="{{ route('admin.tickets.update', $ticket) }}">
                    @csrf @method('PATCH')
                    <label class="form-label" for="response">Tanggapan untuk pelanggan</label>
                    <textarea class="form-control" id="response" name="response" rows="5" maxlength="5000">{{ old('response', $ticket->response) }}</textarea>
                    <label class="form-label mt-3" for="status">Status</label>
                    <select class="form-select" id="status" name="status" required>
                        @foreach (['open', 'answered', 'closed'] as $option)
                            <option value="{{ $option }}" @selected(old('status', $ticket->status) === $option)>@include('admin.partials.status', ['value' => $option, 'plain' => true])</option>
                        @endforeach
                    </select>
                    <button class="btn btn-primary mt-3" type="submit">Simpan tanggapan</button>
                </form>
            </section>
        </div>
    </div>
@endsection
