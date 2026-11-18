@extends('layouts.admin')

@section('title', 'Edit '.$definition['label'].' — Admin Bakery BenBen Co.')

@section('content')
    @php($status = $page->status ?? 'draft')
    <div class="bb-dash-topbar">
        <div>
            <a href="{{ route('admin.pages.index') }}" class="text-choc small"><i class="bi bi-arrow-left"></i> Manajemen Halaman</a>
            <h1 class="section-title mt-2 mb-1" style="font-size:1.6rem;">Edit {{ $definition['label'] }}</h1>
            <p class="section-sub mb-0">Perubahan berstatus draft tidak akan tampil di website sampai dipublikasikan.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="bb-cms-status {{ $status === 'published' ? 'is-published' : 'is-draft' }}">
                {{ $status === 'published' ? 'Published' : 'Draft' }}
            </span>
            <a href="{{ route('admin.pages.preview', $pageKey) }}" class="btn-bb btn-bb-outline btn-bb-sm" target="_blank" rel="noopener">
                <i class="bi bi-eye"></i> Preview
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.pages.update', $pageKey) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row g-4">
            <div class="col-lg-8">
                <section class="bb-panel mb-4">
                    <div class="bb-cms-section-heading">
                        <span>EDITOR KONTEN</span>
                        <h2 class="bb-panel-title mb-0">Konten utama</h2>
                    </div>
                    <div class="mb-3">
                        <label for="page-eyebrow" class="bb-form-label">Label section</label>
                        <input id="page-eyebrow" type="text" name="eyebrow" class="bb-input"
                               value="{{ old('eyebrow', $page->eyebrow ?? $definition['eyebrow']) }}" maxlength="120" required>
                        @error('eyebrow')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="page-title" class="bb-form-label">Judul halaman</label>
                        <input id="page-title" type="text" name="title" class="bb-input"
                               value="{{ old('title', $page->title ?? $definition['title']) }}" maxlength="180" required>
                        @error('title')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div>
                        <label for="page-description" class="bb-form-label">Deskripsi</label>
                        <textarea id="page-description" name="description" class="bb-input" rows="5" maxlength="3000">{{ old('description', $page->description ?? $definition['description']) }}</textarea>
                        @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </section>

                <section class="bb-panel">
                    <div class="bb-cms-section-heading">
                        <span>MEDIA</span>
                        <h2 class="bb-panel-title mb-0">Gambar pengantar</h2>
                    </div>
                    <label for="page-image" class="bb-form-label">Upload gambar (opsional)</label>
                    <input id="page-image" type="file" name="gambar" class="bb-input" accept="image/jpeg,image/png,image/webp">
                    <small class="d-block text-muted mt-2">JPG, PNG, atau WEBP; maksimal 5 MB.</small>
                    @error('gambar')<small class="text-danger d-block">{{ $message }}</small>@enderror
                    @if($page->image_path)
                        <img class="bb-cms-editor-image mt-3"
                             src="{{ route('media.show', ['path' => $page->image_path]) }}"
                             alt="Gambar halaman {{ $definition['label'] }}">
                    @else
                        <div class="bb-cms-upload-empty mt-3">
                            <i class="bi bi-image"></i>
                            <span>Belum ada gambar khusus. Website akan memakai gambar bawaan.</span>
                        </div>
                    @endif
                </section>
            </div>

            <div class="col-lg-4">
                <aside class="bb-panel bb-cms-publish-panel">
                    <span class="eyebrow">PENGATURAN</span>
                    <h2 class="bb-panel-title mt-2">Status halaman</h2>
                    <p class="section-sub small">Simpan sebagai draft untuk meninjau perubahan nanti, atau publikasikan agar tampil pada halaman publik.</p>
                    <div class="bb-cms-status-help">
                        <span class="bb-cms-status is-draft">Draft</span>
                        <span>Hanya dapat dilihat melalui preview admin.</span>
                    </div>
                    <div class="bb-cms-status-help">
                        <span class="bb-cms-status is-published">Published</span>
                        <span>Konten aktif di halaman website terkait.</span>
                    </div>
                    <a href="{{ route('admin.pages.preview', $pageKey) }}" class="bb-cms-preview-link" target="_blank" rel="noopener">
                        <i class="bi bi-box-arrow-up-right"></i> Buka preview tersimpan
                    </a>
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" name="status" value="published" class="btn-bb btn-bb-primary">
                            <i class="bi bi-send-check"></i> Publikasikan
                        </button>
                        <button type="submit" name="status" value="draft" class="btn-bb btn-bb-outline">
                            <i class="bi bi-file-earmark"></i> Simpan Draft
                        </button>
                    </div>
                </aside>
            </div>
        </div>
    </form>
@endsection
