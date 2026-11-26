{{-- Выручка по месяцам за год. Клик по столбцу открывает главную за этот месяц. --}}
<div class="min-w-0 p-4 mb-8 bg-white rounded-lg shadow-xs">
    <div class="flex flex-wrap items-baseline justify-between gap-2 mb-6">
        <h4 class="font-semibold text-gray-800">Выручка по месяцам, {{ $year }}</h4>
        <p class="text-sm text-gray-500">
            За год: <span class="font-semibold text-gray-700">{{ kopToRubView($yearSum) }}</span>
        </p>
    </div>

    {{-- Мышь: наведение — подсказка, клик — переход. Тач: первый тап — подсказка, второй — переход. --}}
    <div class="flex gap-2" x-data="{ active: null, pointer: 'mouse' }" @click.away="active = null">
        {{-- Шкала Y --}}
        <div class="flex flex-col justify-between h-50 text-xs text-right text-gray-400 w-16 shrink-0">
            @foreach([4, 3, 2, 1, 0] as $step)
                <span class="leading-none -translate-y-1/2 first:translate-y-0 last:translate-y-0 whitespace-nowrap">{{ kopToRubShort($scaleMax / 4 * $step) }}</span>
            @endforeach
        </div>

        <div class="flex-1 w-0 overflow-x-auto pb-1">
            <div class="relative min-w-[480px]">
                <div class="absolute inset-x-0 top-0 flex flex-col justify-between h-50 pointer-events-none">
                    @foreach(range(0, 4) as $i)
                        <div class="border-t {{ $i === 4 ? 'border-gray-300' : 'border-dashed border-gray-200' }}"></div>
                    @endforeach
                </div>

                <div class="relative flex items-end h-50 gap-2" @pointerleave="if ($event.pointerType === 'mouse') active = null">
                    @foreach($months as $i => $month)
                        @php
                            $height = $month['sum'] > 0 ? max(2, $month['sum'] / $scaleMax * 100) : 0;
                            $tooltipX = match (true) {
                                $i < 4 => 'left-0',
                                $i >= 8 => 'right-0',
                                default => 'left-1/2 -translate-x-1/2',
                            };
                            $tooltipY = $height > 50
                                ? 'top: ' . (100 - $height) . '%; margin-top: 0.5rem'
                                : 'bottom: ' . $height . '%; margin-bottom: 0.5rem';
                            $barColor = $month['is_selected'] ? 'bg-orange-400' : 'bg-[#8b7bd8]';
                        @endphp
                        <a
                            href="{{ $month['url'] }}"
                            class="relative flex items-end justify-center flex-1 h-full"
                            aria-label="{{ $month['label'] }}: {{ kopToRubView($month['sum']) }}, заказов {{ $month['count'] }}"
                            @pointerenter="if ($event.pointerType === 'mouse') active = {{ $i }}"
                            @pointerdown="pointer = $event.pointerType"
                            @click="if (pointer !== 'mouse' && active !== {{ $i }}) { $event.preventDefault(); active = {{ $i }} }"
                        >
                            <div
                                class="w-full max-w-10 rounded-t-md transition duration-200 {{ $barColor }}"
                                :class="active === {{ $i }} && 'brightness-90'"
                                style="height: {{ $height }}%"
                            ></div>
                            <div
                                class="absolute z-10 {{ $tooltipX }} px-2.5 py-1.5 text-xs text-white whitespace-nowrap bg-gray-800 rounded-md shadow-lg pointer-events-none"
                                style="{{ $tooltipY }}; display: none"
                                x-show="active === {{ $i }}"
                            >
                                <div class="font-semibold">{{ \Illuminate\Support\Str::ucfirst($month['label']) }}</div>
                                <div>Выручка: {{ kopToRubView($month['sum']) }}</div>
                                <div>Заказов: {{ $month['count'] }}</div>
                                @unless($month['is_selected'])
                                    <div class="mt-1 text-gray-300" x-text="pointer === 'mouse' ? 'Клик — открыть месяц' : 'Нажмите ещё раз — открыть месяц'"></div>
                                @endunless
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="flex h-6 gap-2 pt-1.5">
                    @foreach($months as $month)
                        <div class="flex-1 text-center text-[11px] leading-none
                            {{ $month['is_selected'] ? 'font-bold text-orange-500' : ($month['is_current'] ? 'font-semibold text-gray-600' : 'text-gray-400') }}">
                            {{ $month['short'] }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <p class="mt-2 text-xs text-gray-400">Сумма неотменённых заказов по дате выдачи. Оранжевым — выбранный месяц.</p>
</div>
