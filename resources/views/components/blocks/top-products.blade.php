{{-- Популярные товары за период --}}
<div class="min-w-0 p-4 bg-white rounded-lg shadow-xs">
    <h4 class="mb-1 font-semibold text-gray-800">Популярные товары</h4>
    <p class="mb-4 text-xs text-gray-400">В скольких заказах месяца встречается товар (всего заказов: {{ $ordersCount }})</p>

    @if($rows->isEmpty())
        <p class="text-sm text-gray-400">За этот период заказов нет</p>
    @else
        <div class="space-y-3">
            @foreach($rows as $i => $row)
                <div>
                    <div class="flex items-baseline justify-between gap-2 text-sm">
                        <span class="text-gray-700 truncate"><span class="text-gray-400">{{ $i + 1 }}.</span> {{ $row['name'] }}</span>
                        <span class="text-gray-700 whitespace-nowrap">{{ $row['count'] }} <span class="text-gray-400">· {{ $row['share'] }}%</span></span>
                    </div>
                    <div class="h-2 mt-1 overflow-hidden bg-gray-100 rounded-full">
                        <div class="h-full bg-[#8b7bd8] rounded-full" style="width: {{ $row['count'] / $maxCount * 100 }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
