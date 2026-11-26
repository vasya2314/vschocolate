@php(
    $title = "Главная"
)

<x-layouts.main title="{{ $title }}">
    <div class="min-w-0">
        <x-ui.title.h-2 label="{{ $title }}" class="mt-6" />

        <form
            action="{{ route('dashboard.index') }}"
            method="GET"
            class="flex flex-wrap items-end gap-4 mb-6"
        >
            <label class="block text-sm">
                <span class="text-gray-700">Месяц</span>
                <input
                    type="month"
                    name="month"
                    value="{{ $month }}"
                    class="appearance-none p-2.5 rounded-sm border border-gray-300 block mt-1 text-sm form-input bg-white focus:border-purple-400 focus:outline-none focus:shadow-outline-purple"
                    onchange="this.form.submit()"
                />
            </label>

            @if(request()->has('month'))
                <x-ui.form.button-a href="{{ route('dashboard.index') }}" label="Текущий месяц" class="py-2.5" />
            @endif
        </form>

        <p class="mb-4 text-sm text-gray-600">
            Период: {{ $from->format('d.m.Y') }} — {{ $to->format('d.m.Y') }}
        </p>

        <x-blocks.stats :from="$from" :to="$to" />

        <x-blocks.orders-chart :from="$from" :to="$to" />

        <x-blocks.year-revenue :from="$from" :to="$to" />

        <div class="grid gap-6 mb-8 lg:grid-cols-2">
            <x-blocks.sources :from="$from" :to="$to" />
            <x-blocks.top-products :from="$from" :to="$to" />
            <x-blocks.repeat-clients :from="$from" :to="$to" />
            <x-blocks.weekday-load :from="$from" :to="$to" />
        </div>

        <x-blocks.debts />
    </div>
</x-layouts.main>
