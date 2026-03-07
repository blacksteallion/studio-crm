@props([
    'variant' => 'primary', // primary, secondary, danger, success, warning
    'type' => 'button',     // submit, button, reset
    'icon' => null,
    'href' => null
])

@php
    $baseClass = "inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold transition duration-200 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-offset-2";
    
    $variants = [
        'primary'   => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
        'secondary' => 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-gray-200',
        'danger'    => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        'success'   => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
        'warning'   => 'bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-yellow-500',
    ];

    $classes = $baseClass . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon) <i class="{{ $icon }}"></i> @endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon) <i class="{{ $icon }}"></i> @endif
        <span>{{ $slot }}</span>
    </button>
@endif