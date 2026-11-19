@extends('store.layout')

@php
    $titles = [
        'login' => 'Masuk',
        'register' => 'Buat akun',
        'reset-link' => 'Lupa kata sandi',
        'reset' => 'Atur kata sandi baru',
        'verify' => 'Verifikasi email',
        'confirm' => 'Konfirmasi kata sandi',
        'challenge' => 'Verifikasi dua langkah',
    ];
    $actions = [
        'login' => '/login', 'register' => '/register', 'reset-link' => '/forgot-password',
        'reset' => '/reset-password', 'verify' => '/email/verification-notification',
        'confirm' => '/user/confirm-password', 'challenge' => '/two-factor-challenge',
    ];
@endphp

@section('title', $titles[$screen] . ' | Koperasi JSIT')
@section('content')
<section class="mx-auto w-full max-w-lg px-5 py-16">
    <h1 class="mb-6 text-4xl font-semibold">{{ $titles[$screen] }}</h1>
    @if (session('status'))<p role="status" class="mb-4 rounded-md bg-green-100 p-3">Permintaan berhasil diproses.</p>@endif
    @if ($errors->any())
        <ul role="alert" class="mb-4 rounded-md bg-red-100 p-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    @endif
    <form action="{{ url($actions[$screen]) }}" method="POST" class="space-y-5">
        @csrf
        @if ($screen === 'register')
            <label class="block">Nama<input name="name" value="{{ old('name') }}" required autocomplete="name" class="block w-full rounded-md border p-2"></label>
        @endif
        @if (in_array($screen, ['login','register','reset-link','reset'], true))
            <label class="block">Email<input type="email" name="email" value="{{ old('email', $screen === 'reset' ? $request->email : '') }}" required autocomplete="email" class="block w-full rounded-md border p-2"></label>
        @endif
        @if (in_array($screen, ['login','register','reset','confirm'], true))
            <label class="block">Kata sandi<input type="password" name="password" required autocomplete="{{ in_array($screen, ['register','reset'], true) ? 'new-password' : 'current-password' }}" class="block w-full rounded-md border p-2"></label>
        @endif
        @if (in_array($screen, ['register','reset'], true))
            <label class="block">Ulangi kata sandi<input type="password" name="password_confirmation" required autocomplete="new-password" class="block w-full rounded-md border p-2"></label>
            <p class="text-sm">Gunakan minimal 12 karakter.</p>
        @endif
        @if ($screen === 'reset')
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
        @endif
        @if ($screen === 'challenge')
            <label class="block">Kode dari aplikasi autentikator<input name="code" inputmode="numeric" autocomplete="one-time-code" class="block w-full rounded-md border p-2"></label>
            <label class="block">Atau gunakan kode pemulihan<input name="recovery_code" autocomplete="off" class="block w-full rounded-md border p-2"></label>
        @endif
        @if ($screen === 'verify')<p>Periksa email untuk tautan verifikasi akun.</p>@endif
        <button class="rounded-md bg-[#23473C] px-5 py-3 font-medium text-white">{{ $screen === 'verify' ? 'Kirim ulang email' : $titles[$screen] }}</button>
    </form>
    @if ($screen === 'login')
        <nav class="mt-6 flex flex-wrap gap-5 text-sm">
            <a href="{{ route('password.request') }}" class="underline">Lupa kata sandi?</a>
            <a href="{{ route('register') }}" class="underline">Daftar akun</a>
        </nav>
    @endif
    @if ($screen === 'verify')<form action="{{ route('logout') }}" method="POST" class="mt-5">@csrf<button class="underline">Keluar</button></form>@endif
</section>
@endsection
