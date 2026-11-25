@props(['order'])

{{--
    Статус заказа: у активного заказа — выпадающий список, смена сохраняется асинхронно, без перезагрузки.
    Без JS форма отправляется обычным способом (fallback в контроллере).
--}}
@if(is_null($order->deleted_at))
    <form
        action="{{ route('order.updateStatus', $order->id) }}"
        method="POST"
        class="inline-flex items-center gap-1.5"
        x-data="{
            value: @js($order->status),
            classes: @js($order->status_classes),
            state: 'idle', {{-- idle | saving | saved | error --}}
            error: '',
            async save() {
                const previous = this.value;
                const select = this.$refs.select;
                this.state = 'saving';
                try {
                    const { data } = await window.axios.post(this.$el.action, new FormData(this.$el), {
                        headers: { Accept: 'application/json' },
                    });
                    this.value = data.status;
                    this.classes = data.classes;
                    this.state = 'saved';
                    setTimeout(() => { if (this.state === 'saved') this.state = 'idle' }, 1500);
                } catch (e) {
                    select.value = previous;
                    this.error = e.response?.data?.message || 'Не удалось сменить статус';
                    this.state = 'error';
                }
            },
        }"
        @submit.prevent="save()"
    >
        @csrf
        @method('patch')
        <select
            x-ref="select"
            name="status"
            aria-label="Статус заказа №{{ $order->id }}"
            @change="save()"
            :disabled="state === 'saving'"
            :class="[classes, state === 'saving' && 'opacity-50']"
            class="appearance-none cursor-pointer pl-2 pr-6 py-1 text-xs font-semibold leading-tight rounded-full border-0 focus:outline-none focus:shadow-outline-purple transition-opacity"
            style="background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='currentColor' stroke-width='2' d='M4 6l4 4 4-4'/%3E%3C/svg%3E&quot;); background-repeat: no-repeat; background-position: right 0.4rem center; background-size: 0.7rem;"
        >
            @foreach(\App\Models\Order::statuses() as $value => $label)
                <option value="{{ $value }}" @selected($order->status === $value) class="bg-white text-gray-700">{{ $label }}</option>
            @endforeach
        </select>
        <noscript><button type="submit" class="text-xs text-purple-600">OK</button></noscript>
        <i x-show="state === 'saving'" x-cloak class="bi bi-arrow-repeat inline-block animate-spin text-gray-400" aria-hidden="true"></i>
        <i x-show="state === 'saved'" x-cloak x-transition.opacity class="bi bi-check-lg text-green-600" aria-label="Сохранено"></i>
        <i x-show="state === 'error'" x-cloak class="bi bi-exclamation-circle text-red-600 cursor-help" :title="error" :aria-label="error"></i>
    </form>
@else
    <span class="px-2 py-1 text-xs font-semibold leading-tight rounded-full {{ $order->status_classes }}">
        {{ $order->status_view }}
    </span>
@endif
