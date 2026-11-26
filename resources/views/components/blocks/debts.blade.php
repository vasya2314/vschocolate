{{-- Долг клиентов за всё время --}}
<div class="min-w-0 p-4 mb-8 bg-white rounded-lg shadow-xs">
    <div class="flex flex-wrap items-baseline justify-between gap-2 mb-1">
        <h4 class="font-semibold text-gray-800">Долг клиентов</h4>
        <span class="text-xs text-gray-400">за всё время, не зависит от выбранного месяца</span>
    </div>

    <div class="grid gap-4 my-4 sm:grid-cols-2">
        <div>
            <p class="text-sm text-gray-500">Не доплачено по выданным заказам</p>
            <p class="text-2xl font-semibold {{ $totalDebt > 0 ? 'text-red-600' : 'text-gray-700' }}">{{ kopToRubView($totalDebt) }}</p>
            <p class="text-xs text-gray-500">{{ $debtOrders }} зак. у {{ $debtorsCount }} {{ $debtorsCount % 10 === 1 && $debtorsCount % 100 !== 11 ? 'клиента' : 'клиентов' }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Ожидается доплата по невыданным</p>
            <p class="text-2xl font-semibold text-gray-700">{{ kopToRubView($expectedDebt) }}</p>
            <p class="text-xs text-gray-500">{{ $expectedOrders }} зак. со статусом «Принят» или «Выполнен»</p>
        </div>
    </div>

    @if($rows->isNotEmpty())
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap">
                <thead>
                <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                    <th class="px-4 py-2">Клиент</th>
                    <th class="px-4 py-2">Телефон</th>
                    <th class="px-4 py-2">Заказов с долгом</th>
                    <th class="px-4 py-2">Самый старый</th>
                    <th class="px-4 py-2 text-right">Долг</th>
                </tr>
                </thead>
                <tbody class="divide-y text-sm text-gray-700">
                @foreach($rows as $row)
                    <tr>
                        <td class="px-4 py-2">
                            @if(is_null($row['client']->deleted_at))
                                <a href="{{ route('client.edit', $row['client']->id) }}" class="font-medium hover:text-purple-600 hover:underline">{{ $row['client']->name }}</a>
                            @else
                                <span class="text-gray-400">{{ $row['client']->name }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap"><a href="tel:{{ $row['client']->phone }}" class="hover:text-purple-600">{{ $row['client']->phone_view }}</a></td>
                        <td class="px-4 py-2">{{ $row['count'] }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $row['oldest']->format('d.m.Y') }}</td>
                        <td class="px-4 py-2 font-semibold text-right text-red-600 whitespace-nowrap">{{ kopToRubView($row['debt']) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if($hidden > 0)
            <p class="mt-2 text-xs text-gray-500">… и ещё {{ $hidden }} клиентов с долгом</p>
        @endif
    @else
        <p class="text-sm text-gray-400">Должников нет 🎉</p>
    @endif
</div>
