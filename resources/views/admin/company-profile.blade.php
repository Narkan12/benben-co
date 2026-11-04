@extends('layouts.admin')

@section('title', 'Company Profile — Admin Bakery BenBen Co.')

@section('content')
    <div class="bb-dash-topbar">
        <div>
            <span class="eyebrow">Data Master</span>
            <h2 class="section-title" style="font-size:1.6rem;">Gambar Company Profile</h2>
            <p class="section-sub mb-0">Ganti foto yang tampil pada beranda dan halaman Tentang BENBEN CO.</p>
        </div>
    </div>

    <div class="row g-3">
        @foreach($images as $image)
            <div class="col-md-6 col-xl-4">
                <div class="bb-panel h-100">
                    <img src="{{ route('media.show', ['path' => $image->currentPath()]) }}"
                         alt="{{ $image->label }}"
                         class="w-100 rounded mb-3"
                         style="height:190px;object-fit:cover;">
                    <h3 class="h6 mb-1">{{ $image->label }}</h3>
                    <p class="small text-muted mb-3">
                        {{ $image->image_path ? 'Menggunakan gambar yang diunggah.' : 'Menggunakan gambar bawaan.' }}
                    </p>
                    <form method="POST" action="{{ route('admin.company-profile.images.update', $image->image_key) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <label for="image-{{ $image->image_key }}" class="bb-form-label">Pilih gambar baru</label>
                        <input id="image-{{ $image->image_key }}" type="file" name="gambar" class="bb-input mb-2"
                               accept="image/jpeg,image/png,image/webp" required>
                        <small class="d-block text-muted mb-3">JPG, PNG, atau WEBP; maksimal 5 MB.</small>
                        <button type="submit" class="btn-bb btn-bb-primary btn-bb-sm">
                            <i class="bi bi-upload"></i> Simpan Gambar
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endsection
