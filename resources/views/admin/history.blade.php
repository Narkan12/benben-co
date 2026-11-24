@extends('layouts.admin')

@section('title', 'Riwayat Aktivitas — Admin Bakery BenBen Co.')

@section('content')
    @php
        $statusMap = [
            'active' => 'Aktif',
            'logged_out' => 'Logout',
            'expired' => 'Berakhir',
            'failed' => 'Gagal',
            'success' => 'Berhasil',
        ];
    @endphp

    <div class="bb-dash-topbar">
        <div>
            <h2 class="section-title" style="font-size:1.6rem;">Riwayat Aktivitas</h2>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach([
            ['Total aktivitas', $summary['total_activity'], 'bi-clock-history'],
            ['Admin login', $summary['admin_activity'], 'bi-shield-lock'],
            ['Pelanggan login', $summary['customer_activity'], 'bi-person-check'],
            ['Sesi aktif', $summary['active_sessions'], 'bi-wifi'],
            ['Sesi berakhir', $summary['ended_sessions'], 'bi-door-closed'],
        ] as [$label, $value, $icon])
            <div class="col-6 col-xl">
                <div class="bb-stat-card h-100">
                    <span class="ic"><i class="bi {{ $icon }}"></i></span>
                    <div>
                        <div class="val">{{ number_format((int) $value) }}</div>
                        <div class="lbl">{{ $label }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <form method="GET" class="bb-filter-bar">
        <div class="bb-filter-field">
            <label for="activity-search" class="bb-form-label">Cari pengguna atau email</label>
            <input id="activity-search" type="search" name="search" value="{{ request('search') }}" class="bb-input" placeholder="Nama atau email">
        </div>
        <div class="bb-filter-field">
            <label for="activity-type" class="bb-form-label">Jenis aktivitas</label>
            <select id="activity-type" name="type" class="bb-input">
                <option value="">Semua aktivitas</option>
                @foreach($activityTypes as $type)
                    <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
        </div>
        <div class="bb-filter-field">
            <label for="activity-role" class="bb-form-label">Role pengguna</label>
            <select id="activity-role" name="role" class="bb-input">
                <option value="">Semua role</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="kasir" {{ request('role') === 'kasir' ? 'selected' : '' }}>Kasir</option>
                <option value="customer" {{ request('role') === 'customer' ? 'selected' : '' }}>Pelanggan</option>
            </select>
        </div>
        <div class="bb-filter-field">
            <label for="activity-status" class="bb-form-label">Status sesi</label>
            <select id="activity-status" name="status" class="bb-input">
                <option value="">Semua status</option>
                @foreach($statusMap as $key => $label)
                    <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="bb-filter-field">
            <label for="activity-start-date" class="bb-form-label">Dari tanggal</label>
            <input id="activity-start-date" type="date" name="start_date" value="{{ request('start_date') }}" class="bb-input">
        </div>
        <div class="bb-filter-field">
            <label for="activity-end-date" class="bb-form-label">Sampai tanggal</label>
            <input id="activity-end-date" type="date" name="end_date" value="{{ request('end_date') }}" class="bb-input">
        </div>
        <button type="submit" class="btn-bb btn-bb-primary btn-bb-sm">Terapkan Filter</button>
        <a href="{{ route('admin.history') }}" class="btn-bb btn-bb-outline btn-bb-sm">Reset</a>
    </form>

    <div class="bb-panel">
        <div style="overflow-x:auto;">
            <table class="bb-table-bb">
                <thead>
                    <tr>
                        <th>Pengguna</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Aktivitas</th>
                        <th>Waktu</th>
                        <th>IP Address</th>
                        <th>Browser / Perangkat</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->user?->nama ?? $log->user_name ?? 'Sistem' }}</td>
                            <td>{{ $log->user_email ?? $log->user?->email ?? '-' }}</td>
                            <td>{{ $log->role_name ?? $log->user?->role ?? '-' }}</td>
                            <td>{{ $log->activity_type }}<br><small class="text-muted">{{ $log->description }}</small></td>
                            <td>{{ $log->created_at?->format('d M Y H:i') }}</td>
                            <td>{{ $log->ip_address ?? '-' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($log->user_agent ?? $log->device_info ?? '-', 48) }}</td>
                            <td><span class="bb-status {{ $log->status === 'active' ? 'bb-status-ready' : ($log->status === 'failed' ? 'bb-status-cancel' : 'bb-status-process') }}">{{ $statusMap[$log->status] ?? ucfirst((string) $log->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">Belum ada aktivitas yang tercatat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>
@endsection
