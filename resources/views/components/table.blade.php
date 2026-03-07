@props(['headers' => []])

<div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white shadow-sm">
    <table class="w-full text-left border-collapse text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-200 text-xs uppercase text-gray-500 font-bold tracking-wider">
                @foreach($headers as $header)
                    <th class="px-6 py-4 {{ $loop->first ? 'rounded-tl-2xl' : '' }} {{ $loop->last ? 'rounded-tr-2xl text-right' : '' }}">
                        {{ $header }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
            {{ $slot }}
        </tbody>
    </table>

    @if($slot->isEmpty())
        <div class="flex flex-col items-center justify-center py-12 text-gray-400">
            <i class="fas fa-folder-open text-4xl mb-3 opacity-20"></i>
            <p class="text-sm font-medium">No records found.</p>
        </div>
    @endif
    
    @if(isset($pagination))
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            {{ $pagination }}
        </div>
    @endif
</div>