@props(['title' => null])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-gray-200 bg-white shadow-sm h-full']) }}>
    @if($title || isset($action))
        <div class="border-b border-gray-100 px-6 py-4 flex justify-between items-center">
            @if($title)
                <h3 class="font-bold text-slate-800">{{ $title }}</h3>
            @endif
            
            @if(isset($action))
                <div class="text-xs font-bold">
                    {{ $action }}
                </div>
            @endif
        </div>
    @endif

    <div {{ $attributes->merge(['class' => 'p-0']) }}>
        {{ $slot }}
    </div>
</div>