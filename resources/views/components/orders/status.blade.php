@props(['order'])

{{--
    Статус заказа: у активного заказа — выпадающий список, смена сохраняется асинхронно, без перезагрузки.
    Обратная связь — только обводкой (box-shadow) самого бейджа, без дополнительных элементов,
    а ширина бейджа фиксирована: колонки таблицы не сдвигаются.
      сохранение — бейдж полупрозрачный;
      успех      — короткая зелёная обводка;
      ошибка     — красная обводка, статус возвращается к прежнему, текст ошибки во всплывающей подсказке.
    Без JS форма отправляется обычным способом (fallback в контроллере).
--}}
@if(is_null($order->deleted_at))
    <form
        action="{{ route('order.updateStatus', $order->id) }}"
        method="POST"
        class="inline-block"
        x-data="{
            value: @js($order->status),
            classes: @js($order->status_classes),
            state: 'idle', {{-- idle | saving | saved | error --}}
            error: '',
            timer: null,
            flash(state, ms) {
                this.state = state;
                clearTimeout(this.timer);
                this.timer = setTimeout(() => { this.state = 'idle' }, ms);
            },
            async save() {
                const previous = this.value;
                const select = this.$refs.select;
                clearTimeout(this.timer);
                this.state = 'saving';
                this.error = '';
                try {
                    const { data } = await window.axios.post(this.$el.action, new FormData(this.$el), {
                        headers: { Accept: 'application/json' },
                    });
                    this.value = data.status;
                    this.classes = data.classes;
                    this.flash('saved', 1200);
                } catch (e) {
                    select.value = previous;
                    this.error = e.response?.data?.message || 'Не удалось сменить статус';
                    this.flash('error', 4000);
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
            :aria-busy="state === 'saving'"
            :title="error"
            :class="[
                classes,
                state === 'saving' && 'opacity-60 cursor-wait',
                state === 'saved' && 'ring-2 ring-green-400',
                state === 'error' && 'ring-2 ring-red-500',
            ]"
            class="w-[6.5rem] appearance-none cursor-pointer pl-2 pr-6 py-1 text-xs font-semibold leading-tight rounded-full border-0 focus:outline-none focus:shadow-outline-purple transition"
            style="background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='currentColor' stroke-width='2' d='M4 6l4 4 4-4'/%3E%3C/svg%3E&quot;); background-repeat: no-repeat; background-position: right 0.4rem center; background-size: 0.7rem;"
        >
            @foreach(\App\Models\Order::statuses() as $value => $label)
                <option value="{{ $value }}" @selected($order->status === $value) class="bg-white text-gray-700">{{ $label }}</option>
            @endforeach
        </select>
        <noscript><button type="submit" class="text-xs text-purple-600">OK</button></noscript>
    </form>
@else
    <span class="inline-block w-[6.5rem] px-2 py-1 text-xs font-semibold leading-tight rounded-full {{ $order->status_classes }}">
        {{ $order->status_view }}
    </span>
@endif
