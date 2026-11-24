@extends('layouts.admin')

@section('title', 'Monitoring Sesi — Admin Bakery BenBen Co.')

@section('content')
    @php
        $statusLabels = [
            'active' => 'Aktif',
            'logged_out' => 'Sudah logout',
            'expired' => 'Berakhir',
        ];
    @endphp

    <div class="bb-dash-topbar">
        <div>
            <h1 class="section-title" style="font-size:1.6rem;">Monitoring Sesi</h1>
        </div>
    </div>

    <form method="GET" class="bb-filter-bar">
        <div class="bb-filter-field">
            <label for="session-search" class="bb-form-label">Cari pengguna, email, atau IP</label>
            <input id="session-search" type="search" name="search" value="{{ request('search') }}" class="bb-input" placeholder="Nama, email, atau IP">
        </div>
        <div class="bb-filter-field">
            <label for="session-status" class="bb-form-label">Status sesi</label>
            <select id="session-status" name="status" class="bb-input">
                <option value="">Semua status</option>
                @foreach($statusLabels as $key => $label)
                    <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-bb btn-bb-primary btn-bb-sm">Terapkan</button>
        <a href="{{ route('admin.sessions') }}" class="btn-bb btn-bb-outline btn-bb-sm">Reset</a>
    </form>

    <div class="bb-panel">
        <div style="overflow-x:auto;">
            <table class="bb-table-bb">
                <thead>
                    <tr>
                        <th>Pengguna</th>
                        <th>Email</th>
                        <th>Login</th>
                        <th>Aktivitas Terakhir</th>
                        <th>Logout</th>
                        <th>IP Address</th>
                        <th>Browser / Perangkat</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                        <tr>
                            <td>{{ $session->user?->nama ?? '-' }}</td>
                            <td>{{ $session->user?->email ?? '-' }}</td>
                            <td>{{ $session->login_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td>{{ $session->last_activity_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td>{{ $session->logout_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td>{{ $session->ip_address ?? '-' }}</td>
                            <td>{{ $session->user_agent ?
                                Illuminate\Support\Str::limit($session->user_agent, 45) : (
                                    $session->device_info ? Illuminate\Support\Str::limit($session->device_info, 45) : '-'
                                ) }}</td>
                            <td><span class="bb-status {{ $session->status === 'active' ? 'bb-status-ready' : ($session->status === 'expired' ? 'bb-status-cancel' : 'bb-status-process') }}">{{ $statusLabels[$session->status] ?? ucfirst($session->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">Belum ada data sesi pengguna.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $sessions->links() }}
        </div>
    </div>
@endsection
