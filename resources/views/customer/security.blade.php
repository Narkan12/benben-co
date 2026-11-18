@extends('layouts.customer')

@section('title', 'Riwayat Login & Keamanan Akun — BenBen Co.')

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
            <span class="eyebrow">Keamanan akun</span>
            <h1 class="section-title" style="font-size:1.6rem;">Riwayat Login &amp; Keamanan Akun</h1>
            <p class="section-sub mb-0">Pantau perangkat dan sesi yang saat ini terhubung dengan akun Anda.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="bb-stat-card h-100">
                <span class="ic"><i class="bi bi-shield-check"></i></span>
                <div>
                    <div class="val">{{ $sessions->total() }}</div>
                    <div class="lbl">Total sesi</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bb-stat-card h-100">
                <span class="ic"><i class="bi bi-wifi"></i></span>
                <div>
                    <div class="val">{{ $sessions->where('status', 'active')->count() }}</div>
                    <div class="lbl">Sesi aktif</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bb-stat-card h-100">
                <span class="ic"><i class="bi bi-door-closed"></i></span>
                <div>
                    <div class="val">{{ $sessions->whereIn('status', ['logged_out', 'expired'])->count() }}</div>
                    <div class="lbl">Sesi berakhir</div>
                </div>
            </div>
        </div>
    </div>

    <div class="bb-panel">
        <div style="overflow-x:auto;">
            <table class="bb-table-bb">
                <thead>
                    <tr>
                        <th>Waktu login</th>
                        <th>Waktu logout</th>
                        <th>Browser</th>
                        <th>Perangkat</th>
                        <th>IP Address</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                        <tr>
                            <td>{{ $session->login_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td>{{ $session->logout_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td>{{ $session->user_agent ? Illuminate\Support\Str::limit($session->user_agent, 35) : '-' }}</td>
                            <td>{{ $session->device_info ? Illuminate\Support\Str::limit($session->device_info, 35) : '-' }}</td>
                            <td>{{ $session->ip_address ?? '-' }}</td>
                            <td><span class="bb-status {{ $session->status === 'active' ? 'bb-status-ready' : ($session->status === 'expired' ? 'bb-status-cancel' : 'bb-status-process') }}">{{ $statusLabels[$session->status] ?? ucfirst($session->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Belum ada catatan sesi untuk akun Anda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $sessions->links() }}
        </div>
    </div>
@endsection
