@extends('admin.layout')
@section('title', 'Pengaturan toko')

@section('content')
    @php
        $faqRows = array_values((array) old('faqs', $faqs));
        $faqRows = $faqRows ?: [['question' => '', 'answer' => '']];
    @endphp

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="row g-3 align-items-start">
            <div class="col-lg-7">
                <section class="card">
                    <div class="card-header"><h2 class="card-title">Banner beranda</h2></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" for="banner-title">Judul</label>
                            <input class="form-control" id="banner-title" name="banner[title]" maxlength="160" value="{{ old('banner.title', $banner['title'] ?? '') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="banner-description">Deskripsi</label>
                            <textarea class="form-control" id="banner-description" name="banner[description]" maxlength="1000" rows="3">{{ old('banner.description', $banner['description'] ?? '') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="banner-cta">Teks tombol katalog</label>
                            <input class="form-control" id="banner-cta" name="banner[cta]" maxlength="80" value="{{ old('banner.cta', $banner['cta'] ?? '') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="banner-image">Gambar banner</label>
                            <input class="form-control" id="banner-image" name="banner_image" type="file" accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">JPG, PNG, atau WebP hingga 5 MB.</div>
                            @if (!empty($banner['image_path']))
                                <img class="admin-banner mt-3" src="@include('store.partials.image-url', ['path' => $banner['image_path']])" alt="Banner saat ini">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" id="banner-remove-image" name="banner[remove_image]" type="checkbox" value="1" @checked(old('banner.remove_image'))>
                                    <label class="form-check-label" for="banner-remove-image">Hapus gambar saat ini</label>
                                </div>
                            @endif
                        </div>
                        <div class="form-check">
                            <input type="hidden" name="banner[active]" value="0">
                            <input class="form-check-input" id="banner-active" name="banner[active]" type="checkbox" value="1" @checked(old('banner.active', $banner['active'] ?? true))>
                            <label class="form-check-label" for="banner-active">Tampilkan banner</label>
                        </div>
                    </div>
                </section>

                <section class="card">
                    <div class="card-header d-flex justify-content-between align-items-center gap-2">
                        <h2 class="card-title">Pertanyaan umum</h2>
                        <button class="btn btn-sm btn-outline-primary" type="button" data-faq-add><i class="bi bi-plus-lg me-1" aria-hidden="true"></i> Tambah FAQ</button>
                    </div>
                    <div class="card-body d-grid gap-3" data-faq-list data-next-index="{{ count($faqRows) }}">
                        @foreach ($faqRows as $index => $faq)
                            @include('admin.partials.faq-row', ['index' => $index, 'faq' => $faq])
                        @endforeach
                    </div>
                </section>
            </div>
            <div class="col-lg-5">
                <section class="card">
                    <div class="card-header"><h2 class="card-title">Kontak toko</h2></div>
                    <div class="card-body">
                        @foreach (['email' => ['Email', 'email', 150], 'phone' => ['Telepon', 'tel', 40], 'whatsapp' => ['WhatsApp', 'tel', 30]] as $field => [$label, $type, $length])
                            <div class="mb-3">
                                <label class="form-label" for="contact-{{ $field }}">{{ $label }}</label>
                                <input class="form-control" id="contact-{{ $field }}" type="{{ $type }}" name="contact[{{ $field }}]" value="{{ old('contact.'.$field, $contact[$field] ?? '') }}" maxlength="{{ $length }}">
                            </div>
                        @endforeach
                        <label class="form-label" for="contact-address">Alamat toko</label>
                        <textarea class="form-control" id="contact-address" name="contact[address]" maxlength="500" rows="3">{{ old('contact.address', $contact['address'] ?? '') }}</textarea>
                    </div>
                </section>

                <section class="card">
                    <div class="card-header"><h2 class="card-title">Rekening pembayaran</h2></div>
                    <div class="card-body">
                        <p class="text-secondary small">Rekening ini ditampilkan pada instruksi pembayaran pelanggan.</p>
                        @foreach (['name' => ['Nama bank', 100], 'number' => ['Nomor rekening', 40], 'holder' => ['Nama pemilik rekening', 150]] as $field => [$label, $length])
                            <div @class(['mb-3' => !$loop->last])>
                                <label class="form-label" for="bank-{{ $field }}">{{ $label }}</label>
                                <input class="form-control" id="bank-{{ $field }}" name="bank[{{ $field }}]" value="{{ old('bank.'.$field, $bank[$field] ?? '') }}" maxlength="{{ $length }}" @if($field === 'number') inputmode="numeric" @endif>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        </div>
        <button class="btn btn-primary" type="submit">Simpan pengaturan</button>
    </form>

    <template id="faq-template">
        @include('admin.partials.faq-row', ['index' => '__INDEX__', 'faq' => []])
    </template>
@endsection
