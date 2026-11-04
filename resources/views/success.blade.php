@extends('layouts.app')

@section('title', 'Pesanan Berhasil — Bakery BenBen Co.')

@section('content')
    <section class="bb-section-sm">
        <div class="container-bb">
            <div class="bb-panel text-center" style="max-width:720px;margin:0 auto;padding:48px 24px;">
                <div style="font-size:4rem;color:var(--bb-primary);margin-bottom:16px;">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <span class="eyebrow">Pesanan Berhasil</span>
                <h1 class="section-title" style="margin:12px 0 14px;">Terima kasih, pesanan Anda telah diterima.</h1>
                <p class="section-sub mx-auto" style="max-width:540px;">
                    @if($order)
                        Kode pesanan Anda: <strong>{{ $order->kode_order }}</strong>.<br>
                        Tim kami akan segera memproses pesanan Anda.
                    @else
                        Pesanan Anda telah dicatat. Tim kami akan segera memprosesnya.
                    @endif
                </p>

                <div class="d-flex justify-content-center gap-3 flex-wrap mt-4">
                    @if($whatsappUrl)
                        <a href="{{ $whatsappUrl }}" class="btn-bb btn-bb-primary" target="_blank" rel="noopener">
                            Konfirmasi via WhatsApp
                        </a>
                    @endif
                    <a href="{{ url('/products') }}" class="btn-bb btn-bb-primary">Lanjut Belanja</a>
                </div>
            </div>
        </div>
    </section>
@endsection
