@php
    $toneClasses = [
        'good' => 'text-green-600',
        'bad' => 'text-red-600',
        'neutral' => 'text-gray-400',
    ];
@endphp

@if(!empty($items))
    <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-3">
        @foreach($items as $item)
            <!-- Card -->
            <div class="flex items-center p-4 bg-white rounded-lg shadow-xs">
                <div class="p-3 mr-4 {{ $item['classes'] }} rounded-full">
                    <i class="{{ $item['icon'] }} w-5 h-5 flex justify-center items-center text-lg"></i>
                </div>
                <div class="min-w-0">
                    <p class="mb-2 text-sm font-medium text-gray-600">
                        {{ $item['name'] }}
                    </p>
                    <p class="text-lg font-semibold text-gray-700">
                        {{ $item['value'] }}
                    </p>
                    @if(!empty($item['delta']))
                        <p
                            class="mt-1 text-xs {{ $toneClasses[$item['delta']['tone']] }}"
                            @if($item['delta']['title']) title="{{ $item['delta']['title'] }}" @endif
                        >
                            {{ $item['delta']['text'] }}
                            @if($item['delta']['text'] !== '—')
                                <span class="text-gray-400">к прошлому месяцу</span>
                            @endif
                        </p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
