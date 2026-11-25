@php
    $title = "Карточка клиента";
    $cards = [
        ['name' => 'Заказов', 'value' => $stats['active_count'] . ($stats['canceled_count'] ? ' (+' . $stats['canceled_count'] . ' отм.)' : ''), 'icon' => 'bi bi-cart2', 'classes' => 'text-blue-500 bg-blue-100'],
        ['name' => 'Сумма заказов', 'value' => kopToRubView($stats['amount_total']), 'icon' => 'bi bi-cash', 'classes' => 'text-green-500 bg-green-100'],
        ['name' => 'Средний чек', 'value' => kopToRubView($stats['average']), 'icon' => 'bi bi-receipt', 'classes' => 'text-teal-500 bg-teal-100'],
        ['name' => 'Долг', 'value' => kopToRubView($stats['debt']), 'icon' => 'bi bi-exclamation-circle', 'classes' => $stats['debt'] > 0 ? 'text-red-500 bg-red-100' : 'text-gray-500 bg-gray-100'],
    ];
@endphp

<x-layouts.main title="{{ $title }}">
    <div>
        <div class="flex flex-wrap items-end justify-between gap-4 mt-6 mb-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-700">{{ $client->name }}</h2>
                <p class="mt-1 text-sm text-gray-500">
                    <a href="tel:{{ $client->phone }}" class="hover:text-purple-600"><i class="bi bi-telephone mr-1"></i>{{ $client->phone_view }}</a>
                    · {{ $client->fromPlatform?->name }}
                    @if($stats['first_date'])
                        · клиент с {{ $stats['first_date']->format('d.m.Y') }}
                    @endif
                    @if($stats['last_date'])
                        · последний заказ {{ $stats['last_date']->format('d.m.Y') }}
                    @endif
                </p>
            </div>
        </div>

        <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
            @foreach($cards as $card)
                <div class="flex items-center p-4 bg-white rounded-lg shadow-xs">
                    <div class="p-3 mr-4 {{ $card['classes'] }} rounded-full">
                        <i class="{{ $card['icon'] }} w-5 h-5 flex justify-center items-center text-lg"></i>
                    </div>
                    <div>
                        <p class="mb-2 text-sm font-medium text-gray-600">{{ $card['name'] }}</p>
                        <p class="text-lg font-semibold text-gray-700">{{ $card['value'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <x-ui.title.h-3 label="История заказов" />
        @if($orders->isNotEmpty())
            <div class="w-full overflow-hidden rounded-lg shadow-xs mb-8">
                <div class="w-full overflow-x-auto">
                    <table class="w-full whitespace-no-wrap">
                        <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Дата выдачи</th>
                            <th class="px-4 py-3">Тело заказа</th>
                            <th class="px-4 py-3">Сумма</th>
                            <th class="px-4 py-3">Оплачено</th>
                            <th class="px-4 py-3">Статус</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y">
                        @foreach($orders as $order)
                            <tr class="text-gray-700">
                                <td class="px-4 py-3">{{ $order->id }}</td>
                                <td class="px-4 py-3 text-sm whitespace-nowrap">{{ $order->date_issue_view }}</td>
                                <td class="px-4 py-3 text-sm min-w-[200px]">
                                    {{ $order->products->pluck('name')->implode(', ') ?: '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm whitespace-nowrap">{{ $order->amount_total_view }}</td>
                                <td class="px-4 py-3 text-sm whitespace-nowrap {{ $order->debt > 0 && $order->status !== \App\Models\Order::STATUS_CANCELED ? 'text-red-600' : '' }}">
                                    {{ $order->amount_payed_view }}
                                </td>
                                <td class="px-4 py-3 text-xs whitespace-nowrap">
                                    <x-orders.status :order="$order" />
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('order.edit', $order->id) }}" class="text-blue-600 hover:text-blue-800" aria-label="Открыть заказ №{{ $order->id }}">
                                        <i class="bi bi-pencil inline-block text-xl"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <x-common.paginate :data="$orders" />
            </div>
        @else
            <span class="block font-light text-gray-500 mb-8">У клиента пока нет заказов</span>
        @endif

        <x-ui.title.h-3 label="Данные клиента" />
        <div
            class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md"
        >
            <form action="{{ route('client.update', $client->id) }}" method="POST">
                @csrf
                @method('patch')
                <x-ui.form.input label="ID" placeholder="ID" type="text" name="id" class="mb-4" disabled value="{{ $client->id }}" />
                <x-ui.form.input label="Имя" placeholder="Имя" required="required" type="text" name="name" class="mb-4" value="{{ $client->name }}" />
                <x-ui.form.input label="Телефон" placeholder="Телефон" required="required" type="tel" name="phone" class="mb-4" value="{{ $client->phone }}" />
                <x-ui.form.select label="Площадка" required="required" :options="$fromPlatforms" value="{{ $client->fromPlatform->id }}" name="from_platform_id" class="mb-4"/>
                <x-ui.form.textarea label="Комментарий" placeholder="Комментарий" name="comment" value="{{ $client->comment }}"/>

                <x-ui.form.button label="Обновить" type="submit" class="mt-4" />
            </form>
        </div>
    </div>
</x-layouts.main>
