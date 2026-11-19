@extends('store.layout')
@section('title', 'Hubungi Kami')

@section('content')
    @include('store.partials.page-intro', ['heading' => 'Hubungi Kami', 'trail' => ['Hubungi Kami' => null], 'description' => 'Tanyakan tentang produk, pemesanan, atau pengambilan di toko.'])
    <div class="shell page-body narrow-page">
        @php($contact = $settings->get('contact', []))
        <section class="panel form-panel" aria-labelledby="contact-title">
            <h2 id="contact-title">Kontak Koperasi JSIT</h2>
            <ul class="contact-list">
                @if(filled($contact['email'] ?? null))<li>@include('store.partials.icon', ['name' => 'mail'])<div><strong>Email</strong><a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></div></li>@endif
                @if(filled($contact['phone'] ?? null))<li>@include('store.partials.icon', ['name' => 'phone'])<div><strong>Telepon</strong><a href="tel:{{ preg_replace('/[^+0-9]/', '', $contact['phone']) }}">{{ $contact['phone'] }}</a></div></li>@endif
                @if(filled($contact['whatsapp'] ?? null))<li>@include('store.partials.icon', ['name' => 'phone'])<div><strong>WhatsApp</strong><a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contact['whatsapp']) }}">{{ $contact['whatsapp'] }}</a></div></li>@endif
                @if(filled($contact['address'] ?? null))<li>@include('store.partials.icon', ['name' => 'pin'])<div><strong>Alamat Toko</strong><span>{{ $contact['address'] }}</span></div></li>@endif
            </ul>
            <p class="field-help">Untuk bantuan terkait pesanan, buka pesanan Anda dan gunakan formulir tiket bantuan.</p>
            <a href="{{ route('store.orders.lookup') }}" class="button button-secondary">Lacak Pesanan @include('store.partials.icon', ['name' => 'arrow'])</a>
        </section>
    </div>
@endsection
