@php
    $pageTitle = "Календарь заказов";
    $weekdays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
    $dotClasses = [
        \App\Models\Order::STATUS_ACCEPT => 'bg-orange-400',
        \App\Models\Order::STATUS_PROGRESS => 'bg-gray-400',
        \App\Models\Order::STATUS_COMPLETE => 'bg-green-500',
        \App\Models\Order::STATUS_CANCELED => 'bg-red-400',
    ];
    $tabClass = fn (bool $active) => $active
        ? 'bg-purple-600 text-white'
        : 'bg-white text-gray-600 hover:bg-gray-100';
@endphp

<x-layouts.main title="{{ $pageTitle }}">
    <x-ui.title.h-2 label="{{ $pageTitle }}" class="mt-6" />

    {{-- Навигация --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
        <div class="flex items-center gap-2">
            <a href="{{ route('order.calendar', ['view' => $mode, 'date' => $prev]) }}"
               class="flex items-center justify-center w-9 h-9 bg-white rounded-lg shadow-xs text-gray-600 hover:bg-gray-100"
               aria-label="Назад">
                <i class="bi bi-chevron-left"></i>
            </a>
            <a href="{{ route('order.calendar', ['view' => $mode, 'date' => $next]) }}"
               class="flex items-center justify-center w-9 h-9 bg-white rounded-lg shadow-xs text-gray-600 hover:bg-gray-100"
               aria-label="Вперёд">
                <i class="bi bi-chevron-right"></i>
            </a>
            <h3 class="ml-2 text-lg font-semibold text-gray-700">{{ $title }}</h3>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('order.calendar', ['view' => $mode]) }}"
               class="px-3 py-2 text-sm font-medium bg-white rounded-lg shadow-xs text-gray-600 hover:bg-gray-100">
                Сегодня
            </a>
            <div class="flex overflow-hidden rounded-lg shadow-xs text-sm font-medium">
                <a href="{{ route('order.calendar', ['view' => 'month', 'date' => $date]) }}"
                   class="px-3 py-2 {{ $tabClass($mode === 'month') }}">Месяц</a>
                <a href="{{ route('order.calendar', ['view' => 'week', 'date' => $date]) }}"
                   class="px-3 py-2 {{ $tabClass($mode === 'week') }}">Неделя</a>
            </div>
            <a href="{{ route('order.create') }}"
               class="px-3 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                <i class="bi bi-plus-lg"></i><span class="hidden sm:inline ml-1">Заказ</span>
            </a>
        </div>
    </div>

    {{-- Легенда статусов --}}
    <div class="flex flex-wrap gap-4 mb-4 text-xs text-gray-600">
        @foreach($statuses as $value => $label)
            <span class="inline-flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full {{ $dotClasses[$value] }}"></span>{{ $label }}
            </span>
        @endforeach
    </div>

    {{-- Сетка (планшет и десктоп) --}}
    <div class="hidden md:block mb-8 overflow-hidden bg-white rounded-lg shadow-xs">
        <div class="grid grid-cols-7 text-xs font-semibold tracking-wide text-center text-gray-500 uppercase border-b bg-gray-50">
            @foreach($weekdays as $i => $weekday)
                <div class="py-2 {{ $i >= 5 ? 'text-[#8b7bd8]' : '' }}">{{ $weekday }}</div>
            @endforeach
        </div>
        <div class="grid grid-cols-7">
            @foreach($days as $day)
                <div class="border-b border-r border-gray-100 p-1.5 {{ $mode === 'week' ? 'min-h-[24rem]' : 'min-h-[8rem]' }} {{ $day['in_period'] ? '' : 'bg-gray-50' }}">
                    <div class="flex items-center justify-between mb-1">
                        <a href="{{ route('order.index', ['date_from' => $day['date'], 'date_to' => $day['date']]) }}"
                           title="Заказы за {{ $day['label'] }}"
                           class="flex items-center justify-center w-6 h-6 text-xs rounded-full hover:bg-purple-100
                               {{ $day['is_today'] ? 'bg-orange-400 text-white font-bold hover:bg-orange-500' : ($day['in_period'] ? 'text-gray-700' : 'text-gray-400') }}">
                            {{ $day['day'] }}
                        </a>
                        @if($day['orders']->isNotEmpty())
                            <span class="text-[10px] text-gray-400">{{ $day['sum'] }}</span>
                        @endif
                    </div>
                    <div class="space-y-1">
                        @foreach($day['orders'] as $order)
                            <a href="{{ route('order.edit', $order->id) }}"
                               title="№{{ $order->id }} · {{ $order->client->name }} · {{ $order->amount_total_view }} · {{ $order->status_view }}"
                               class="flex items-center gap-1 px-1.5 py-0.5 text-xs rounded hover:bg-gray-100 {{ $order->status === \App\Models\Order::STATUS_CANCELED ? 'line-through text-gray-400' : 'text-gray-700' }}">
                                <span class="w-2 h-2 shrink-0 rounded-full {{ $dotClasses[$order->status] ?? 'bg-gray-300' }}"></span>
                                <span class="shrink-0 text-gray-400">{{ \Carbon\Carbon::parse($order->date_issue)->format('H:i') }}</span>
                                <span class="truncate">{{ $order->client->name }}</span>
                            </a>
                            @if($mode === 'week')
                                <div class="pl-4 pb-1 text-[11px] text-gray-500">
                                    {{ $order->amount_total_view }}
                                    @if($order->debt > 0)
                                        · <span class="text-red-500">долг {{ kopToRubView($order->debt) }}</span>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Список по дням (телефон) --}}
    @php($phoneDays = array_filter($days, fn ($d) => $d['in_period'] && ($d['orders']->isNotEmpty() || $d['is_today'])))
    <div class="md:hidden mb-8 space-y-4">
        @forelse($phoneDays as $day)
            <div class="overflow-hidden bg-white rounded-lg shadow-xs">
                <a href="{{ route('order.index', ['date_from' => $day['date'], 'date_to' => $day['date']]) }}"
                   class="flex items-center justify-between px-4 py-2 text-sm font-semibold border-b
                       {{ $day['is_today'] ? 'bg-orange-50 text-orange-600' : 'bg-gray-50 text-gray-700' }}">
                    <span>{{ $day['label'] }} @if($day['is_today']) · сегодня @endif</span>
                    @if($day['orders']->isNotEmpty())
                        <span class="text-xs font-normal text-gray-500">{{ $day['orders']->count() }} · {{ $day['sum'] }}</span>
                    @endif
                </a>
                @forelse($day['orders'] as $order)
                    <a href="{{ route('order.edit', $order->id) }}" class="flex items-center gap-3 px-4 py-2.5 text-sm border-b last:border-b-0 border-gray-100">
                        <span class="w-2.5 h-2.5 shrink-0 rounded-full {{ $dotClasses[$order->status] ?? 'bg-gray-300' }}"></span>
                        <span class="text-gray-400">{{ \Carbon\Carbon::parse($order->date_issue)->format('H:i') }}</span>
                        <span class="flex-1 truncate {{ $order->status === \App\Models\Order::STATUS_CANCELED ? 'line-through text-gray-400' : 'text-gray-700' }}">{{ $order->client->name }}</span>
                        <span class="text-gray-500 whitespace-nowrap">{{ $order->amount_total_view }}</span>
                    </a>
                @empty
                    <p class="px-4 py-2.5 text-sm text-gray-400">Заказов нет</p>
                @endforelse
            </div>
        @empty
            <span class="block font-light text-red-500">Заказов за этот период нет</span>
        @endforelse
    </div>
</x-layouts.main>
