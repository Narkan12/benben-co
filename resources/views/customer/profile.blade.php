@extends('layouts.customer')

@section('title', 'Profil Saya — Bakery BenBen Co.')

@section('content')
<div class="bb-dash-topbar">
    <div>
        <span class="eyebrow">Akun</span>
        <h2 class="section-title" style="font-size:1.6rem;">Profil Saya</h2>
    </div>
</div>

<div class="bb-panel" style="max-width:720px;">
    <form method="POST" action="{{ route('customer.profile.update') }}" class="row g-3">
        @csrf
        @method('PATCH')

        <div class="col-12 d-flex align-items-center gap-3 pb-2 border-bottom mb-2">
            <img src="{{ route('media.show', ['path' => 'images/logo.png']) }}" alt="Foto Profil" style="width:72px;height:72px;border-radius:50%;object-fit:cover;background:var(--bb-cream);">
            <div>
                <div class="fw-semibold text-choc">{{ $customer?->nama ?? auth()->user()->nama }}</div>
                <small class="text-muted">Informasi akun pelanggan</small>
            </div>
        </div>

        <div class="col-md-6 bb-input-group">
            <label for="nama" class="bb-form-label">Nama Lengkap</label>
            <input id="nama" name="nama" type="text" class="bb-input" value="{{ old('nama', $customer?->nama ?? auth()->user()->nama) }}" required>
        </div>

        <div class="col-md-6 bb-input-group">
            <label for="username" class="bb-form-label">Username</label>
            <input id="username" type="text" class="bb-input" value="{{ auth()->user()->username }}" readonly>
        </div>

        <div class="col-md-6 bb-input-group">
            <label for="email" class="bb-form-label">Email</label>
            <input id="email" name="email" type="email" class="bb-input" value="{{ old('email', auth()->user()->email) }}" required>
        </div>

        <div class="col-md-6 bb-input-group">
            <label for="no_whatsapp" class="bb-form-label">Nomor WhatsApp</label>
            <input id="no_whatsapp" name="no_whatsapp" type="tel" class="bb-input" value="{{ old('no_whatsapp', $customer?->no_whatsapp) }}" required>
        </div>

        <div class="col-12 bb-input-group">
            <label for="alamat" class="bb-form-label">Alamat</label>
            <textarea id="alamat" name="alamat" class="bb-input" rows="3" placeholder="Masukkan alamat lengkap Anda">{{ old('alamat', $customer?->alamat) }}</textarea>
        </div>

        <div class="col-12 d-flex flex-wrap gap-2">
            <button type="submit" class="btn-bb btn-bb-primary">Simpan Perubahan</button>
            <a href="{{ route('products') }}" class="btn-bb btn-bb-outline">Mulai Belanja</a>
        </div>
    </form>
</div>
@endsection
