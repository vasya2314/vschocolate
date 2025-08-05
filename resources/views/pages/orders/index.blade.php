@php
    $title = "Заказы";
@endphp

<x-layouts.main title="{{ $title }}">
    <x-ui.title.h-2 label="{{ $title }}" class="mt-6" />
    <div class="md:flex">
        <x-ui.form.button-a href="{{ route('order.create') }}" label="Создать заказ" class="py-3" />
        <x-ui.form.button-blue @click="toggleFilter" type="button" label="Открыть фильтр" class="py-3 mt-4 w-full md:ml-4 md:mt-0 md:w-max" />

        @if(!empty(request()->except('page')))
            <x-ui.form.button-a href="{{ route('order.index') }}" label="Сбросить фильтр" class="py-3 mt-4 w-full md:ml-4 md:mt-0 md:w-max" />
        @endif

    </div>
    @if($orders->isNotEmpty())
        <div class="w-full overflow-hidden rounded-lg shadow-xs mt-4">
            <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                    <thead>
                    <tr
                        class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50"
                    >
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Дата выдачи</th>
                        <th class="px-4 py-3">Клиент</th>
                        <th class="px-4 py-3">Тело заказа</th>
                        <th class="px-4 py-3">Описание</th>
                        <th class="px-4 py-3">Сумма заказа</th>
                        <th class="px-4 py-3">Оплаченная сумма</th>
                        <th class="px-4 py-3">Статус</th>
                        <th class="px-4 py-3">Дата создания</th>
                        <th class="px-4 py-3">Действия</th>
                    </tr>
                    </thead>
                    <tbody
                        class="bg-white divide-y"
                    >
                    @foreach($orders as $order)
                        <tr class="{{ $order->deleted_at ? 'bg-gray-100 border-gray-300 text-gray-400' : 'bg-white border-gray-200 text-gray-700' }}">

                            <td class="px-4 py-3" style="position: sticky;left: 0; z-index: 10; background: inherit;">{{ $order->id }}</td>
                            <td class="px-4 py-3 text-sm">{{ $order->date_issue_view }}</td>
                            <td class="px-4 py-3 text-sm whitespace-nowrap
                                @if(!is_null($order->client->deleted_at)) {{ 'text-red-600' }} @endif
                            ">
                                <p class="font-semibold">{{ $order->client->name }}</p>
                                {{ $order->client->phone_view }}
                            </td>
                            <td class="px-4 py-3 text-sm min-w-[200px]">
                                @if($order->products->isNotEmpty())
                                    @foreach($order->products as $product)
                                        <p class="@if(!is_null($product->deleted_at)) {{ 'text-red-600' }} @endif">
                                            {{ $product->name }} </br>
                                        </p>
                                    @endforeach
                                @else
                                    {{ '-' }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm min-w-[250px]">
                                {{ $order->short_description }}
                            </td>
                            <td class="px-4 py-3 text-sm whitespace-nowrap">
                                {{ $order->amount_total_view }}
                            </td>
                            <td class="px-4 py-3 text-sm whitespace-nowrap">
                                {{ $order->amount_payed_view }}
                            </td>
                            <td class="px-4 py-3 text-xs">
                                <span class="px-2 py-1 font-semibold leading-tight rounded-full
                                    @if($order->status == \App\Models\Order::STATUS_ACCEPT) {{ 'text-orange-700 bg-orange-100' }} @endif
                                    @if($order->status == \App\Models\Order::STATUS_CANCELED) {{ 'text-red-700 bg-red-100' }} @endif
                                    @if($order->status == \App\Models\Order::STATUS_PROGRESS) {{ 'text-gray-700 bg-gray-100' }} @endif
                                    @if($order->status == \App\Models\Order::STATUS_COMPLETE) {{ 'text-green-700 bg-green-100' }} @endif
                                ">
                                    {{ $order->status_view }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{ $order->created_at_view }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center space-x-4 text-sm">
                                    @if(is_null($order->deleted_at))
                                        <a
                                            href="{{ route('order.edit', $order->id) }}"
                                            class="text-blue-600 hover:text-blue-800 cursor-pointer"
                                        >
                                            <i class="bi bi-pencil inline-block text-xl"></i>
                                        </a>
                                        <form action="{{ route('order.delete', $order->id) }}" method="POST" onsubmit="return confirm('Вы действительно хотите удалить заказ?');">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="text-red-600 hover:text-red-800 cursor-pointer">
                                                <i class="bi bi-trash3 inline-block text-xl"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('order.restore', $order->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-gray-500 hover:text-gray-700 cursor-pointer">
                                                <i class="bi bi-arrow-clockwise inline-block w-7 h-7 text-xl"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <x-common.paginate :data="$orders" />
        </div>
    @else
        <span class="block font-light text-red-500 mt-4">Не найдено ни одной записи</span>
    @endif
</x-layouts.main>

<x-layouts.filter action="{{ route('order.index') }}">
    <x-ui.form.select
        label="Статус"
        :options="$statuses"
        name="statuses"
        class="mb-4"
        multiple
        :value="request('statuses')"
    />
    <x-ui.form.select
        label="Товары"
        :options="$products"
        name="products"
        class="mb-4"
        multiple
        :value="request('products')"
    />
</x-layouts.filter>
