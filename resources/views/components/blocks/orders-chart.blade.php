<div class="p-4 mb-8 bg-white rounded-lg shadow-xs">
    <div class="flex flex-wrap items-baseline justify-between gap-2 mb-6">
        <h4 class="font-semibold text-gray-800">Заказы по дням</h4>
        <p class="text-sm text-gray-500">
            Всего: <span class="font-semibold text-gray-700">{{ $totalCount }}</span>
            на <span class="font-semibold text-gray-700">{{ $totalSum }}</span>
        </p>
    </div>

    <div class="flex gap-2">
        {{-- Шкала Y --}}
        <div class="flex flex-col justify-between h-56 pb-6 text-xs text-right text-gray-400 w-6 shrink-0">
            @foreach([4, 3, 2, 1, 0] as $step)
                <span class="leading-none -translate-y-1/2 first:translate-y-0 last:translate-y-0">{{ $scaleMax / 4 * $step }}</span>
            @endforeach
        </div>

        <div class="relative flex-1 min-w-0">
            {{-- Линии сетки --}}
            <div class="absolute inset-x-0 top-0 flex flex-col justify-between h-50 pointer-events-none">
                @foreach(range(0, 4) as $i)
                    <div class="border-t {{ $i === 4 ? 'border-gray-300' : 'border-dashed border-gray-200' }}"></div>
                @endforeach
            </div>

            {{-- Столбцы --}}
            <div class="relative flex items-end h-50 gap-0.5 sm:gap-1">
                @foreach($days as $day)
                    @php($height = $day['count'] > 0 ? max(2, $day['count'] / $scaleMax * 100) : 0)
                    <div class="relative flex items-end justify-center flex-1 h-full group">
                        <div
                            class="w-full max-w-6 rounded-t-md transition-all duration-200
                                {{ $day['is_today']
                                    ? 'bg-orange-400'
                                    : 'bg-[#8b7bd8] group-hover:bg-[#7563cc]' }}"
                            style="height: {{ $height }}%"
                        ></div>

                        {{-- Подсказка --}}
                        <div class="absolute z-10 px-2.5 py-1.5 mb-2 text-xs text-white whitespace-nowrap bg-gray-800 rounded-md shadow-lg opacity-0 pointer-events-none transition-opacity group-hover:opacity-100"
                             style="bottom: {{ $height }}%">
                            <div class="font-semibold">{{ $day['label'] }}</div>
                            <div>Заказов: {{ $day['count'] }}</div>
                            <div>Сумма: {{ $day['sum'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Подписи дней --}}
            <div class="flex h-6 gap-0.5 sm:gap-1 pt-1.5">
                @foreach($days as $day)
                    <div class="flex-1 text-center text-[10px] leading-none
                        {{ $day['is_today'] ? 'font-bold text-orange-500' : ($day['is_weekend'] ? 'text-[#8b7bd8]' : 'text-gray-400') }}
                        {{ ($day['day'] === 1 || $day['day'] % 5 === 0 || $day['is_today']) ? '' : 'invisible sm:visible' }}">
                        {{ $day['day'] }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
