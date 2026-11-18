@extends('layouts.admin')

@section('title', 'Kelola Pengguna — Admin Bakery BenBen Co.')

@section('content')
    <div class="bb-dash-topbar">
        <div>
            <span class="eyebrow">Admin Panel</span>
            <h2 class="section-title" style="font-size:1.6rem;">Kelola Pengguna</h2>
        </div>
    </div>

    <div class="bb-panel mb-4">
        <h5 class="bb-panel-title">Tambah Pengguna</h5>
        <form method="POST" action="{{ route('admin.users.store') }}" class="row g-3">
            @csrf
            <div class="col-md-3">
                <label class="bb-form-label">Nama</label>
                <input type="text" name="nama" class="bb-input" required>
            </div>
            <div class="col-md-3">
                <label class="bb-form-label">Username</label>
                <input type="text" name="username" class="bb-input" required>
            </div>
            <div class="col-md-3">
                <label class="bb-form-label">Email</label>
                <input type="email" name="email" class="bb-input">
            </div>
            <div class="col-md-3">
                <label class="bb-form-label">Role</label>
                <select name="role" class="bb-input" required>
                    <option value="admin">Admin</option>
                    <option value="kasir">Kasir</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="bb-form-label">Password</label>
                <input type="password" name="password" class="bb-input" required>
            </div>
            <div class="col-md-4">
                <label class="bb-form-label">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" class="bb-input" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn-bb btn-bb-primary btn-bb-sm">Tambah Pengguna</button>
            </div>
        </form>
    </div>

    <form method="GET" class="bb-filter-bar">
        <input type="search" name="search" value="{{ request('search') }}" class="bb-input" placeholder="Cari pengguna..." style="max-width:220px;">
        <select name="role" class="bb-input">
            <option value="">Semua role</option>
            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="kasir" {{ request('role') === 'kasir' ? 'selected' : '' }}>Kasir</option>
        </select>
        <button type="submit" class="btn-bb btn-bb-primary btn-bb-sm">Terapkan Filter</button>
        <a href="{{ route('admin.users') }}" class="btn-bb btn-bb-outline btn-bb-sm">Reset</a>
    </form>

    <div class="bb-panel">
        <div style="overflow-x:auto;">
            <table class="bb-table-bb">
                <thead>
                    <tr><th>Nama</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Daftar</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->nama }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->email ?? '-' }}</td>
                            <td>{{ ucfirst($user->role) }}</td>
                            <td>{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                            <td>{{ $user->created_at?->format('d M Y') ?? '-' }}</td>
                            <td class="d-flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('admin.users.toggle-status', $user->id_user) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-bb btn-bb-outline btn-bb-sm" {{ auth()->id() === $user->id_user ? 'disabled' : '' }}>
                                        {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.destroy', $user->id_user) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-bb btn-bb-outline btn-bb-sm" onclick="return confirm('Hapus akun ini?')" {{ auth()->id() === $user->id_user ? 'disabled' : '' }}>
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">Belum ada pengguna yang cocok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>
@endsection
