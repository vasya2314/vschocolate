<div class="p-4 mb-8 bg-white rounded-lg shadow-xs">
    <div class="flex flex-wrap items-baseline justify-between gap-2 mb-6">
        <h4 class="font-semibold text-gray-800">Заказы по дням</h4>
        <p class="text-sm text-gray-500">
            Всего: <span class="font-semibold text-gray-700">{{ $totalCount }}</span>
            на <span class="font-semibold text-gray-700">{{ $totalSum }}</span>
        </p>
    </div>

    {{--
        active — индекс дня, для которого открыта подсказка.
        Мышь: наведение. Тач: только тап по столбцу (прокрутка подсказку не открывает); тап мимо графика закрывает.
    --}}
    <div
        class="flex gap-2"
        x-data="{ active: null }"
        @click.away="active = null"
    >
        {{-- Шкала Y — вне скролла, поэтому остаётся на месте --}}
        <div class="flex flex-col justify-between h-50 text-xs text-right text-gray-400 w-6 shrink-0">
            @foreach([4, 3, 2, 1, 0] as $step)
                <span class="leading-none -translate-y-1/2 first:translate-y-0 last:translate-y-0">{{ $scaleMax / 4 * $step }}</span>
            @endforeach
        </div>

        {{-- Горизонтальный скролл только у области графика. w-0 + flex-1: не распирает grid-контейнер страницы --}}
        <div class="flex-1 w-0 overflow-x-auto pb-1">
            <div class="relative min-w-[640px]">
                {{-- Линии сетки --}}
                <div class="absolute inset-x-0 top-0 flex flex-col justify-between h-50 pointer-events-none">
                    @foreach(range(0, 4) as $i)
                        <div class="border-t {{ $i === 4 ? 'border-gray-300' : 'border-dashed border-gray-200' }}"></div>
                    @endforeach
                </div>

                {{-- Столбцы --}}
                <div
                    class="relative flex items-end h-50 gap-1"
                    @pointerleave="if ($event.pointerType === 'mouse') active = null"
                >
                    @foreach($days as $i => $day)
                        @php
                            $height = $day['count'] > 0 ? max(2, $day['count'] / $scaleMax * 100) : 0;
                            // По горизонтали: у краёв прижимаем к краю, чтобы подсказка не обрезалась
                            $tooltipX = match (true) {
                                $i < count($days) / 3 => 'left-0',
                                $i >= count($days) * 2 / 3 => 'right-0',
                                default => 'left-1/2 -translate-x-1/2',
                            };
                            // По вертикали: у высоких столбцов открываем вниз, иначе обрежет скролл-контейнер
                            $tooltipY = $height > 50
                                ? 'top: ' . (100 - $height) . '%; margin-top: 0.5rem'
                                : 'bottom: ' . $height . '%; margin-bottom: 0.5rem';
                            $barColor = $day['is_today'] ? 'bg-orange-400' : 'bg-[#8b7bd8]';
                        @endphp
                        <div
                            class="relative flex items-end justify-center flex-1 h-full cursor-pointer"
                            data-day="{{ $i }}"
                            {{-- Наведение — только настоящей мышью: на таче браузер эмулирует mouseenter при тапе/скролле --}}
                            @pointerenter="if ($event.pointerType === 'mouse') active = {{ $i }}"
                            {{-- click не срабатывает при прокрутке пальцем, поэтому на мобильных подсказка — только по тапу --}}
                            @click="active = {{ $i }}"
                        >
                            <div
                                class="w-full max-w-6 rounded-t-md transition duration-200 {{ $barColor }}"
                                :class="active === {{ $i }} && 'brightness-90'"
                                style="height: {{ $height }}%"
                            ></div>

                            {{-- Подсказка --}}
                            <div
                                class="absolute z-10 {{ $tooltipX }} px-2.5 py-1.5 text-xs text-white whitespace-nowrap bg-gray-800 rounded-md shadow-lg pointer-events-none"
                                style="{{ $tooltipY }}; display: none"
                                x-show="active === {{ $i }}"
                            >
                                <div class="font-semibold">{{ $day['label'] }}</div>
                                <div>Заказов: {{ $day['count'] }}</div>
                                <div>Сумма: {{ $day['sum'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Подписи дней --}}
                <div class="flex h-6 gap-1 pt-1.5">
                    @foreach($days as $day)
                        <div class="flex-1 text-center text-[10px] leading-none
                            {{ $day['is_today'] ? 'font-bold text-orange-500' : ($day['is_weekend'] ? 'text-[#8b7bd8]' : 'text-gray-400') }}">
                            {{ $day['day'] }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
