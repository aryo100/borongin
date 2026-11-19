@extends('admin.layout')
@section('title', 'Anggota sekolah')

@section('content')
    <div class="card">
        <div class="card-header">
            <form class="admin-filter" style="--filter-selects: 1" method="GET" action="{{ route('admin.memberships.index') }}">
                <div><label for="q" class="form-label">Cari pengajuan</label><input class="form-control" id="q" name="q" type="search" value="{{ $query }}" placeholder="Nama, email, atau sekolah"></div>
                <div>
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Semua status</option>
                        @foreach (['pending', 'approved', 'rejected'] as $option)
                            <option value="{{ $option }}" @selected($status === $option)>@include('admin.partials.status', ['value' => $option, 'plain' => true])</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-outline-primary" type="submit">Terapkan</button>
            </form>
        </div>
        @forelse ($memberships as $membership)
            <article class="card-body @unless($loop->first) border-top @endunless">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                    <div>
                        <h2 class="h6 mb-1">{{ $membership->school_name }}</h2>
                        <p class="text-secondary mb-1">{{ $membership->user->name }} · {{ $membership->user->email }}</p>
                        <p class="text-secondary small mb-0">Diperbarui {{ $membership->updated_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</p>
                    </div>
                    @include('admin.partials.status', ['value' => $membership->status])
                </div>
                @if ($membership->status === 'pending')
                    <details class="mt-3" @if((string) old('membership_id') === (string) $membership->id) open @endif>
                        <summary class="link-primary">Tinjau pengajuan</summary>
                        <form method="POST" action="{{ route('admin.memberships.update', $membership) }}" class="mt-2">
                            @csrf @method('PATCH')
                            <input type="hidden" name="membership_id" value="{{ $membership->id }}">
                            <label for="membership-{{ $membership->id }}-note" class="form-label">Catatan</label>
                            <textarea class="form-control" id="membership-{{ $membership->id }}-note" name="note" rows="2" maxlength="1000" aria-describedby="membership-{{ $membership->id }}-help">{{ (string) old('membership_id') === (string) $membership->id ? old('note') : '' }}</textarea>
                            <p class="form-text" id="membership-{{ $membership->id }}-help">Isi alasan jika pengajuan ditolak.</p>
                            <div class="d-flex gap-2 flex-wrap">
                                <button class="btn btn-sm btn-success" name="status" value="approved" type="submit">Setujui</button>
                                <button class="btn btn-sm btn-outline-danger" name="status" value="rejected" type="submit">Tolak</button>
                            </div>
                        </form>
                    </details>
                @elseif ($membership->note)
                    <p class="admin-break-lines mb-0 mt-2">{{ $membership->note }}</p>
                @endif
            </article>
        @empty
            <p class="admin-empty mb-0">Belum ada pengajuan yang cocok.</p>
        @endforelse
        @include('admin.partials.pagination', ['items' => $memberships])
    </div>
@endsection
