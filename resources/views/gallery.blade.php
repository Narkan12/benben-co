@extends('layouts.app')

@section('title', 'Gallery — BENBEN CO. Bakery')

@section('meta_description', 'Lihat pilihan foto produk dan cerita BENBEN CO. Bakery.')

@section('content')
    <section class="profile-gallery section-cream" aria-labelledby="gallery-title">
        <div class="container-bb">
            <div class="profile-section-heading profile-section-heading-row">
                <div>
                    <span class="profile-eyebrow"><span></span> A little look into our kitchen</span>
                    <h2 id="gallery-title">Sepotong cerita,<br><em>dari dapur kami.</em></h2>
                    <p>Kenali lebih dekat sajian BENBEN CO. melalui pilihan foto produk.</p>
                </div>
                <a href="{{ route('products') }}" class="profile-text-link">Lihat katalog <span aria-hidden="true">↗</span></a>
            </div>
            <div class="profile-gallery-grid">
                @foreach($galleryImages as $index => $image)
                    <button type="button" class="profile-gallery-item {{ $index === 0 ? 'profile-gallery-tall' : '' }}"
                            aria-label="Perbesar foto {{ $image['label'] }}" data-lightbox>
                        <img src="{{ route('media.show', ['path' => $image['path']]) }}" alt="{{ $image['label'] }} BENBEN CO." loading="lazy">
                        <span>{{ $image['label'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </section>
@endsection
