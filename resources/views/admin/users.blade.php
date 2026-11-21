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
                                <button type="button" class="btn-bb btn-bb-outline btn-bb-sm"
                                    data-bs-toggle="modal" data-bs-target="#userEditModal" data-edit-user
                                    data-action="{{ route('admin.users.update', $user->id_user) }}"
                                    data-name="{{ $user->nama }}" data-username="{{ $user->username }}"
                                    data-email="{{ $user->email ?? '' }}" data-role="{{ $user->role }}">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('admin.users.toggle-status', $user->id_user) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-bb btn-bb-outline btn-bb-sm" {{ auth()->id() === $user->id_user ? 'disabled' : '' }}>
                                        {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                                <button type="button" class="btn-bb btn-bb-outline btn-bb-sm"
                                    data-bs-toggle="modal" data-bs-target="#crud-delete-confirm-modal"
                                    data-crud-delete data-delete-action="{{ route('admin.users.destroy', $user->id_user) }}"
                                    data-delete-name="{{ $user->nama }}"
                                    {{ auth()->id() === $user->id_user ? 'disabled' : '' }}>
                                    Hapus
                                </button>
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

    <div class="modal fade" id="userEditModal" tabindex="-1" aria-labelledby="userEditModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bb-crud-modal">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="userEditModalTitle">Edit Pengguna</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Batal"></button>
                </div>
                <form method="POST" action="#" data-user-edit-form>
                    @csrf
                    <input type="hidden" name="_method" value="PUT" data-user-edit-method>
                    <div class="modal-body">
                        <div class="bb-input-group">
                            <label for="edit-user-name" class="bb-form-label">Nama</label>
                            <input id="edit-user-name" type="text" name="nama" class="bb-input" required maxlength="100">
                        </div>
                        <div class="bb-input-group">
                            <label for="edit-user-username" class="bb-form-label">Username</label>
                            <input id="edit-user-username" type="text" name="username" class="bb-input" required maxlength="50">
                        </div>
                        <div class="bb-input-group">
                            <label for="edit-user-email" class="bb-form-label">Email</label>
                            <input id="edit-user-email" type="email" name="email" class="bb-input" maxlength="150">
                        </div>
                        <div class="bb-input-group">
                            <label for="edit-user-role" class="bb-form-label">Role</label>
                            <select id="edit-user-role" name="role" class="bb-input" required>
                                <option value="admin">Admin</option>
                                <option value="kasir">Kasir</option>
                            </select>
                        </div>
                        <div class="bb-input-group">
                            <label for="edit-user-password" class="bb-form-label">Password baru (opsional)</label>
                            <input id="edit-user-password" type="password" name="password" class="bb-input" minlength="8" autocomplete="new-password">
                        </div>
                        <div class="bb-input-group">
                            <label for="edit-user-password-confirmation" class="bb-form-label">Konfirmasi password baru</label>
                            <input id="edit-user-password-confirmation" type="password" name="password_confirmation" class="bb-input" minlength="8" autocomplete="new-password">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-bb btn-bb-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-bb btn-bb-primary" data-user-edit-submit disabled>Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-crud-delete-modal />
@endsection
