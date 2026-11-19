@extends('store.layout')
@section('title', 'Member Sekolah')

@section('content')
    @include('store.partials.page-intro', ['heading' => 'Member Sekolah', 'trail' => ['Member Sekolah' => null], 'description' => 'Akses koleksi khusus sekolah dengan keanggotaan Koperasi JSIT.'])
    <div class="shell page-body">
        @include('store.partials.account-nav')
        <div class="membership-grid">
            <div class="membership-photo"><img src="{{ asset('images/brand/jsit-school-batik-mobile.webp') }}" alt="Anak-anak sekolah Islam terpadu mengenakan batik JSIT" width="600" height="520"></div>
            <section class="panel form-panel membership-panel" aria-labelledby="membership-title">
                @guest
                    <h2 id="membership-title">Daftarkan Sekolah Anda</h2>
                    <p class="panel-description">Masuk dengan akun Koperasi JSIT untuk mengajukan keanggotaan sekolah. Setelah disetujui, Anda dapat memesan produk khusus member.</p>
                    @if(Route::has('login'))<a class="button button-primary" href="{{ route('login') }}">Masuk @include('store.partials.icon', ['name' => 'arrow'])</a>@endif
                    @if(Route::has('register'))<a class="text-link membership-link" href="{{ route('register') }}">Buat akun</a>@endif
                @else
                    @if($membership?->status === 'approved')
                        <p class="eyebrow">Keanggotaan aktif</p><h2 id="membership-title">{{ $membership->school_name }}</h2>
                        <p class="panel-description">Keanggotaan sekolah Anda disetujui. Produk khusus member kini dapat Anda pesan melalui katalog.</p>
                        <a class="button button-primary" href="{{ route('store.catalog') }}">Jelajahi Katalog @include('store.partials.icon', ['name' => 'arrow'])</a>
                    @elseif($membership?->status === 'pending')
                        <p class="eyebrow">Dalam peninjauan</p><h2 id="membership-title">{{ $membership->school_name }}</h2>
                        <p class="panel-description">Pengajuan Anda sedang diperiksa oleh Koperasi JSIT. Status terbaru akan muncul di halaman ini.</p>
                    @else
                        <h2 id="membership-title">Daftarkan Sekolah Anda</h2>
                        @if($membership?->status === 'rejected')<p class="notice notice-error">Pengajuan sebelumnya belum disetujui. {{ $membership->note }}</p>@endif
                        <p class="panel-description">Cantumkan nama lengkap sekolah Anda untuk kami tinjau.</p>
                        <form class="stack-form" method="POST" action="{{ route('store.membership.apply') }}">
                            @csrf
                            <div class="field"><label for="school-name">Nama sekolah</label><input id="school-name" name="school_name" value="{{ old('school_name', $membership?->school_name) }}" autocomplete="organization" maxlength="150" required></div>
                            <button class="button button-primary" type="submit">Ajukan Keanggotaan @include('store.partials.icon', ['name' => 'arrow'])</button>
                        </form>
                    @endif
                @endguest
            </section>
        </div>
    </div>
@endsection
