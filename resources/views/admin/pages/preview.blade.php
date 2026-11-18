@extends('layouts.app')

@section('title', 'Preview '.$definition['label'].' — BENBEN CO. Bakery')

@section('content')
    @php
        $title = $page->title ?? $definition['title'];
        $eyebrow = $page->eyebrow ?? $definition['eyebrow'];
        $description = $page->description ?? $definition['description'];
        $status = $page->status ?? 'draft';
    @endphp
    <section class="bb-cms-preview">
        <div class="bb-cms-preview-toolbar">
            <span><i class="bi bi-eye"></i> Preview admin · {{ $definition['label'] }}</span>
            <span class="bb-cms-status {{ $status === 'published' ? 'is-published' : 'is-draft' }}">
                {{ $status === 'published' ? 'Published' : 'Draft' }}
            </span>
            <a href="{{ route('admin.pages.edit', $pageKey) }}" class="btn-bb btn-bb-outline btn-bb-sm">Kembali ke editor</a>
        </div>
        <article class="bb-cms-preview-content">
            <span class="profile-eyebrow"><span></span> {{ $eyebrow }}</span>
            <h1>{{ $title }}</h1>
            <p>{{ $description }}</p>
            @if($page->image_path)
                <img class="bb-cms-page-image"
                     src="{{ route('media.show', ['path' => $page->image_path]) }}"
                     alt="{{ $title }}">
            @endif
            <a href="{{ match($pageKey) {
                'home', 'favorites' => route('home').($pageKey === 'favorites' ? '#produk' : ''),
                'about' => route('about'),
                'promotions' => route('promotions'),
                'contact' => route('contact'),
            } }}" class="btn-bb btn-bb-primary btn-bb-sm mt-4" target="_blank" rel="noopener">
                Lihat halaman publik
            </a>
        </article>
    </section>
@endsection
