@php
    $title = "Изменение заказа";
@endphp

<x-layouts.main title="{{ $title }}">
    <div>
        <x-ui.title.h-2 label="{{ $title }}" class="mt-6" />
        <div
            class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md"
        >
            <form action="{{ route('order.update', $order->id) }}" method="POST">
                @csrf
                @method('patch')
                <x-ui.form.input label="Имя клиента" placeholder="Имя клиента" type="text" name="id" class="mb-4" disabled value="{{ $order->client->name }}" />
                <x-ui.form.input label="Телефон клиента" placeholder="Телефон клиента" type="tel" name="phone" disabled class="mb-4" value="{{ $order->client->phone }}" />

                <x-ui.form.input label="ID заказа" placeholder="ID" type="text" name="id" class="mb-4" disabled value="{{ $order->id }}" />
                <x-ui.form.select label="Статус заказа" placeholder="Статус заказа" required="required" :options="$statuses"  value="{{ $order->status }}" name="status" class="mb-4"/>
                <x-ui.form.input label="Дата выдачи" placeholder="Дата выдачи" required="required" type="datetime-local" name="date_issue" class="mb-4"  value="{{ $order->date_issue }}" />
                <x-ui.form.input label="Сумма заказа" placeholder="Сумма заказа" required="required" type="text" name="amount_total" class="mb-4" isAmount="true"  value="{{ kopToRub($order->amount_total) }}" />
                <x-ui.form.input label="Оплаченная сумма заказа" placeholder="Оплаченная сумма заказа" required="required" type="text" name="amount_payed" class="mb-4" isAmount="true"  value="{{ kopToRub($order->amount_payed) }}" />
                <x-ui.form.select label="Товары" required="required" :options="$products" name="product_ids" multiple class="mb-4"  :value="$order->products->pluck('id')->toArray()"/>
                <x-ui.form.textarea label="Описание заказа" placeholder="Описание заказа" required="required" name="description" class="mb-4" value="{{ $order->description }}" />

                <x-ui.form.button label="Обновить" type="submit" class="mt-4" />
            </form>
        </div>
    </div>
</x-layouts.main>
