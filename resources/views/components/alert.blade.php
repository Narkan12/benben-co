@props([
    'type' => 'info', // success | danger | warning | info
    'dismissible' => true,
])

@php
    $icons = [
        'success' => 'bi-check-circle-fill',
        'danger'  => 'bi-x-circle-fill',
        'warning' => 'bi-exclamation-triangle-fill',
        'info'    => 'bi-info-circle-fill',
    ];
@endphp

<div class="bb-alert bb-alert-{{ $type }}" role="alert">
    <i class="bi {{ $icons[$type] ?? $icons['info'] }}"></i>
    <div class="flex-grow-1">{{ $slot }}</div>
    @if($dismissible)
        <button type="button" class="btn-close" style="font-size:.75rem;" onclick="this.closest('.bb-alert').remove()" aria-label="Tutup"></button>
    @endif
</div>
