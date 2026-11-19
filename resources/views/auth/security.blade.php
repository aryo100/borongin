@extends('store.layout')

@section('title', 'Keamanan akun | Koperasi JSIT')
@section('content')
<section class="mx-auto max-w-xl px-5 py-16">
    <h1 class="mb-6 text-4xl font-semibold">Verifikasi dua langkah</h1>
    <p class="mb-6">Akun admin memerlukan kode autentikator untuk setiap sesi.</p>
    @if ($errors->any())
        <ul role="alert" class="mb-4 rounded-md bg-red-100 p-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    @endif
    @if (! auth()->user()->two_factor_secret)
        <form action="{{ url('/user/two-factor-authentication') }}" method="POST">@csrf <button class="rounded-md bg-[#23473C] px-5 py-3 text-white">Aktifkan autentikator</button></form>
    @elseif (! auth()->user()->two_factor_confirmed_at)
        <p class="mb-5">Pindai kode QR dengan aplikasi autentikator lalu masukkan kode enam digit.</p>
        <div class="mb-5 inline-block rounded-md bg-white p-3">{!! auth()->user()->twoFactorQrCodeSvg() !!}</div>
        <form action="{{ url('/user/confirmed-two-factor-authentication') }}" method="POST" class="space-y-3">
            @csrf
            <label class="block">Kode verifikasi<input name="code" inputmode="numeric" autocomplete="one-time-code" required class="block w-full rounded-md border p-2"></label>
            <button class="rounded-md bg-[#23473C] px-5 py-3 text-white">Konfirmasi kode</button>
        </form>
    @else
        <p>Simpan kode pemulihan, lalu keluar dan masuk kembali menggunakan kode autentikator.</p>
        <details class="my-4"><summary>Tampilkan kode pemulihan</summary><ul class="font-mono">@foreach(auth()->user()->recoveryCodes() as $code)<li>{{ $code }}</li>@endforeach</ul></details>
        @if (session('admin_mfa_user') === auth()->id())<a href="/admin" class="underline">Buka panel admin</a>@endif
        <form action="{{ route('logout') }}" method="POST" class="mt-5">@csrf <button class="rounded-md bg-[#23473C] px-5 py-3 text-white">Keluar</button></form>
    @endif
</section>
@endsection
