{{-- Нагрузка по дням недели --}}
<div class="min-w-0 p-4 bg-white rounded-lg shadow-xs">
    <h4 class="mb-1 font-semibold text-gray-800">Нагрузка по дням недели</h4>
    <p class="mb-4 text-xs text-gray-400">Сколько заказов в среднем на один такой день · {{ $periodLabel }}</p>

    @if($total === 0)
        <p class="text-sm text-gray-400">За этот период заказов нет</p>
    @else
        <div class="space-y-2.5">
            @foreach($days as $day)
                <div class="flex items-center gap-3 text-sm" title="{{ $day['name'] }}: всего {{ $day['count'] }} заказов на {{ kopToRubView($day['sum']) }}">
                    <span class="w-6 shrink-0 {{ $day['is_weekend'] ? 'text-[#8b7bd8] font-medium' : 'text-gray-500' }}">{{ $day['short'] }}</span>
                    <div class="flex-1 h-2 overflow-hidden bg-gray-100 rounded-full">
                        <div class="h-full bg-[#8b7bd8] rounded-full" style="width: {{ $day['avg'] / $maxAvg * 100 }}%"></div>
                    </div>
                    <span class="w-12 text-right text-gray-700 shrink-0">{{ number_format($day['avg'], 1, ',', '') }}</span>
                </div>
            @endforeach
        </div>
        @if($busiest)
            <p class="mt-4 text-xs text-gray-500">Самый загруженный день — <span class="font-medium text-gray-700">{{ mb_strtolower($busiest) }}</span>.</p>
        @endif
    @endif
</div>
