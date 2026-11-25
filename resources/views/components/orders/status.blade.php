@props(['order'])

{{-- Статус заказа: у активного заказа — выпадающий список, смена сохраняется сразу --}}
@if(is_null($order->deleted_at))
    <form action="{{ route('order.updateStatus', $order->id) }}" method="POST" class="inline-block">
        @csrf
        @method('patch')
        <select
            name="status"
            aria-label="Статус заказа №{{ $order->id }}"
            onchange="this.form.submit()"
            class="appearance-none cursor-pointer pl-2 pr-6 py-1 text-xs font-semibold leading-tight rounded-full border-0 focus:outline-none focus:shadow-outline-purple {{ $order->status_classes }}"
            style="background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='currentColor' stroke-width='2' d='M4 6l4 4 4-4'/%3E%3C/svg%3E&quot;); background-repeat: no-repeat; background-position: right 0.4rem center; background-size: 0.7rem;"
        >
            @foreach(\App\Models\Order::statuses() as $value => $label)
                <option value="{{ $value }}" @selected($order->status === $value) class="bg-white text-gray-700">{{ $label }}</option>
            @endforeach
        </select>
    </form>
@else
    <span class="px-2 py-1 text-xs font-semibold leading-tight rounded-full {{ $order->status_classes }}">
        {{ $order->status_view }}
    </span>
@endif
