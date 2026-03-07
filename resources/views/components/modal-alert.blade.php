@props([
    'name' => null,
    'type' => 'success', 
    'title' => '', 
    'message' => '', 
    'show' => false
])

@php
    $colors = [
        'success' => [
            'icon_bg' => 'bg-green-100',
            'icon_text' => 'text-green-600',
            'button' => 'bg-green-600 hover:bg-green-700',
            'icon' => 'fa-check'
        ],
        'danger' => [
            'icon_bg' => 'bg-red-100',
            'icon_text' => 'text-red-600',
            'button' => 'bg-red-600 hover:bg-red-700',
            'icon' => 'fa-times'
        ],
        'warning' => [
            'icon_bg' => 'bg-yellow-100',
            'icon_text' => 'text-yellow-600',
            'button' => 'bg-yellow-500 hover:bg-yellow-600',
            'icon' => 'fa-exclamation'
        ],
        'info' => [
            'icon_bg' => 'bg-blue-100',
            'icon_text' => 'text-blue-600',
            'button' => 'bg-blue-600 hover:bg-blue-700',
            'icon' => 'fa-info'
        ],
    ];
    $theme = $colors[$type] ?? $colors['success'];
@endphp

<div x-data="{ show: {{ $show ? 'true' : 'false' }} }"
     x-show="show"
     @if($name) x-on:{{ $name }}.window="show = true" @endif
     x-on:close-modal.window="show = false"
     x-cloak
     class="fixed inset-0 z-[999] flex items-center justify-center bg-gray-500 bg-opacity-50 backdrop-blur-sm transition-opacity">
    
    <div class="relative w-full max-w-sm scale-100 transform rounded-2xl bg-white p-8 text-center shadow-2xl transition-all sm:w-96"
         x-show="show"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

        <button @click="show = false" class="absolute right-4 top-4 flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-600">
            <i class="fas fa-times text-sm"></i>
        </button>

        <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-3xl {{ $theme['icon_bg'] }} {{ $theme['icon_text'] }}">
            <i class="fas {{ $theme['icon'] }} text-3xl"></i>
        </div>

        <h3 class="mb-2 text-xl font-bold text-slate-800">{{ $title }}</h3>
        
        @if($message)
            <p class="mb-6 text-sm text-gray-500 font-medium leading-relaxed">
                {{ $message }}
            </p>
        @endif

        @if(isset($content))
            <div class="mb-6 text-sm text-gray-500 font-medium leading-relaxed">
                {{ $content }}
            </div>
        @endif

        <div class="flex justify-center gap-3">
            {{ $slot }}
            
            @if($slot->isEmpty() && !$message && !isset($content))
                <button @click="show = false" class="inline-flex items-center justify-center rounded-xl px-8 py-2.5 text-sm font-bold text-white transition shadow-sm {{ $theme['button'] }}">
                    {{ $attributes->get('buttonText', 'Okay, Got It') }}
                </button>
            @elseif($slot->isEmpty() && ($message || isset($content)))
                 <button @click="show = false" class="inline-flex items-center justify-center rounded-xl px-8 py-2.5 text-sm font-bold text-white transition shadow-sm {{ $theme['button'] }}">
                    {{ $attributes->get('buttonText', 'Okay, Got It') }}
                </button>
            @endif
        </div>
    </div>
</div>