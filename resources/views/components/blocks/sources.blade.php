{{-- Источники клиентов за период --}}
<div class="min-w-0 p-4 bg-white rounded-lg shadow-xs">
    <h4 class="mb-1 font-semibold text-gray-800">Источники клиентов</h4>
    <p class="mb-4 text-xs text-gray-400">Новые клиенты и выручка по площадке клиента за месяц</p>

    @if($rows->isEmpty())
        <p class="text-sm text-gray-400">За этот период данных нет</p>
    @else
        <div class="space-y-3">
            @foreach($rows as $row)
                <div>
                    <div class="flex items-baseline justify-between gap-2 text-sm">
                        <span class="font-medium text-gray-700 truncate">{{ $row['name'] }}</span>
                        <span class="text-gray-700 whitespace-nowrap">{{ kopToRubView($row['revenue']) }} <span class="text-gray-400">· {{ $row['share'] }}%</span></span>
                    </div>
                    <div class="h-2 mt-1 overflow-hidden bg-gray-100 rounded-full">
                        <div class="h-full bg-[#8b7bd8] rounded-full" style="width: {{ $row['revenue'] / $maxRevenue * 100 }}%"></div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Новых клиентов: {{ $row['clients'] }} · заказов: {{ $row['orders'] }}
                    </p>
                </div>
            @endforeach
        </div>
    @endif
</div>
