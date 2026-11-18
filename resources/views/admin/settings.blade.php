@extends('layouts.admin')

@section('title', 'Pengaturan Akun — Admin Bakery BenBen Co.')

@section('content')
    <div class="bb-dash-topbar">
        <div>
            <span class="eyebrow">Admin Panel</span>
            <h2 class="section-title" style="font-size:1.6rem;">Pengaturan Akun</h2>
        </div>
    </div>

    <div class="bb-panel" style="max-width:760px;">
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            @method('PATCH')
            <h5 class="bb-panel-title">Profil Admin</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="admin-name" class="bb-form-label">Nama</label>
                    <input id="admin-name" type="text" name="nama" class="bb-input @error('nama') is-invalid @enderror" value="{{ old('nama', auth()->user()->nama) }}" required maxlength="100">
                </div>
            </div>

            <hr class="my-4">
            <h5 class="bb-panel-title">Ganti Kata Sandi (opsional)</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="new-password" class="bb-form-label">Kata Sandi Baru</label>
                    <input id="new-password" type="password" name="password" class="bb-input @error('password') is-invalid @enderror" minlength="8" autocomplete="new-password">
                </div>
                <div class="col-md-6">
                    <label for="new-password-confirmation" class="bb-form-label">Ulangi Kata Sandi Baru</label>
                    <input id="new-password-confirmation" type="password" name="password_confirmation" class="bb-input @error('password') is-invalid @enderror" minlength="8" autocomplete="new-password">
                </div>
            </div>
            <button type="submit" class="btn-bb btn-bb-primary mt-4">Simpan Pengaturan</button>
        </form>
    </div>
@endsection
