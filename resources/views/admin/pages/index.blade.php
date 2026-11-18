@extends('layouts.admin')

@section('title', 'Manajemen Halaman — Admin Bakery BenBen Co.')

@section('content')
    <div class="bb-dash-topbar">
        <div>
            <span class="eyebrow">Konten Website</span>
            <h1 class="section-title" style="font-size:1.6rem;">Manajemen Halaman</h1>
            <p class="section-sub mb-0">Kelola judul, deskripsi, dan gambar pengantar halaman publik.</p>
        </div>
    </div>

    <div class="row g-3">
        @foreach($pages as $page)
            @php($status = $page['content']?->status ?? 'draft')
            <div class="col-md-6 col-xl-4">
                <article class="bb-panel h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <span class="eyebrow">HALAMAN WEBSITE</span>
                            <h2 class="bb-panel-title mt-2 mb-0">{{ $page['label'] }}</h2>
                        </div>
                        <span class="bb-cms-status {{ $status === 'published' ? 'is-published' : 'is-draft' }}">
                            {{ $status === 'published' ? 'Published' : 'Draft' }}
                        </span>
                    </div>
                    <p class="section-sub small mb-4">
                        {{ $page['content']?->title ?? 'Konten bawaan website masih digunakan.' }}
                    </p>
                    <div class="d-flex gap-2 mt-auto">
                        <a href="{{ route('admin.pages.edit', $page['key']) }}" class="btn-bb btn-bb-primary btn-bb-sm">
                            <i class="bi bi-pencil-square"></i> Edit halaman
                        </a>
                        <a href="{{ route('admin.pages.preview', $page['key']) }}" class="btn-bb btn-bb-outline btn-bb-sm" target="_blank" rel="noopener">
                            Preview
                        </a>
                    </div>
                </article>
            </div>
        @endforeach
    </div>
@endsection
