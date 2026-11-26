{{-- Повторные клиенты --}}
<div class="min-w-0 p-4 bg-white rounded-lg shadow-xs">
    <h4 class="mb-1 font-semibold text-gray-800">Повторные клиенты</h4>
    <p class="mb-4 text-xs text-gray-400">Повторный — у кого больше одного неотменённого заказа за всё время</p>

    @if($periodOrders > 0)
        <div class="flex items-baseline gap-2">
            <span class="text-3xl font-semibold text-gray-700">{{ $repeatShare }}%</span>
            <span class="text-sm text-gray-500">заказов месяца — от повторных клиентов</span>
        </div>
        <div class="flex h-2 mt-3 overflow-hidden rounded-full bg-gray-100" role="img" aria-label="Повторные: {{ $repeatOrders }} из {{ $periodOrders }} заказов">
            <div class="h-full bg-[#8b7bd8]" style="width: {{ $repeatShare }}%"></div>
        </div>
        <p class="mt-2 text-xs text-gray-500">
            {{ $repeatOrders }} из {{ $periodOrders }} заказов ·
            повторных клиентов: {{ $repeatClients }} из {{ $periodClients }}
        </p>
    @else
        <p class="text-sm text-gray-400">За этот период заказов нет</p>
    @endif

    <h5 class="mt-6 mb-2 text-sm font-semibold text-gray-700">Топ-10 клиентов за всё время</h5>
    @if($top->isEmpty())
        <p class="text-sm text-gray-400">Заказов пока нет</p>
    @else
        <ol class="text-sm divide-y divide-gray-100">
            @foreach($top as $i => $row)
                <li class="flex items-center justify-between gap-2 py-1.5">
                    <span class="min-w-0 truncate">
                        <span class="text-gray-400">{{ $i + 1 }}.</span>
                        @if(is_null($row['client']->deleted_at))
                            <a href="{{ route('client.edit', $row['client']->id) }}" class="text-gray-700 hover:text-purple-600 hover:underline">{{ $row['client']->name }}</a>
                        @else
                            <span class="text-gray-400">{{ $row['client']->name }}</span>
                        @endif
                        <span class="text-xs text-gray-400">{{ $row['client']->phone_view }}</span>
                    </span>
                    <span class="text-gray-700 whitespace-nowrap">{{ kopToRubView($row['total']) }} <span class="text-gray-400">· {{ $row['count'] }} зак.</span></span>
                </li>
            @endforeach
        </ol>
    @endif
</div>
