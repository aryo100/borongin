@extends('store.layout')
@section('title', 'Lacak Pesanan')

@section('content')
    @include('store.partials.page-intro', ['heading' => 'Lacak Pesanan', 'trail' => ['Lacak Pesanan' => null]])
    <div class="shell page-body narrow-page">
        <section class="panel form-panel" aria-labelledby="find-order-heading">
            <h2 id="find-order-heading">Kirim ulang tautan pesanan</h2>
            <p class="panel-description">Masukkan nomor pesanan dan email yang digunakan saat checkout. Kami akan mengirim tautan untuk membuka detail pesanan ke email Anda.</p>
            <form method="POST" action="{{ route('store.orders.lookup.send') }}" class="stack-form">
                @csrf
                <div class="field"><label for="lookup-id">Nomor pesanan</label><input id="lookup-id" name="public_id" value="{{ old('public_id') }}" autocapitalize="characters" spellcheck="false" required></div>
                <div class="field"><label for="lookup-email">Email pemesanan</label><input id="lookup-email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" required></div>
                <button class="button button-primary" type="submit">Kirim Tautan @include('store.partials.icon', ['name' => 'arrow'])</button>
            </form>
        </section>
    </div>
@endsection
