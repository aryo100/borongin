@extends('store.layout')
@section('title', 'Pertanyaan Umum')

@section('content')
    @include('store.partials.page-intro', ['heading' => 'Pertanyaan Umum', 'trail' => ['Pertanyaan Umum' => null]])
    <div class="shell page-body narrow-page">
        @php($faqs = $settings->get('faqs', []))
        @forelse($faqs as $faq)
            <details class="faq-item">
                <summary>{{ $faq['question'] }} @include('store.partials.icon', ['name' => 'plus'])</summary>
                <div>{{ $faq['answer'] }}</div>
            </details>
        @empty
            <div class="panel form-panel"><h2>Butuh bantuan?</h2><p class="panel-description">Tim Koperasi JSIT dapat membantu pertanyaan tentang produk, pesanan, dan pengiriman.</p><a class="button button-primary" href="{{ route('store.contact') }}">Hubungi Kami</a></div>
        @endforelse
        @if($faqs)<p class="help-footer">Masih punya pertanyaan? <a class="text-link" href="{{ route('store.contact') }}">Hubungi kami @include('store.partials.icon', ['name' => 'arrow'])</a></p>@endif
    </div>
@endsection
