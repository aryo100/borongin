@extends('store.layout')
@section('title', 'Profil Saya')

@section('content')
    @include('store.partials.page-intro', ['heading' => 'Profil Saya', 'trail' => ['Profil' => null]])
    <div class="shell page-body">
        @include('store.partials.account-nav')
        <section class="panel form-panel" aria-labelledby="profile-title">
            <h2 id="profile-title">Informasi akun</h2>
            <p class="panel-description">Email {{ auth()->user()->email }}</p>
            <form class="stack-form" method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')
                <div class="field">
                    <label for="profile-name">Nama</label>
                    <input id="profile-name" name="name" value="{{ old('name', auth()->user()->name) }}" autocomplete="name" maxlength="120" required>
                </div>
                <button class="button button-primary" type="submit">Simpan Profil</button>
            </form>
            <a class="text-link membership-link" href="{{ route('security') }}">Keamanan akun</a>
        </section>
    </div>
@endsection
