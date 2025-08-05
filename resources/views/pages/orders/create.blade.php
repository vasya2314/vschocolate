@php
    $title = "Создание заказа";
@endphp

<x-layouts.main title="{{ $title }}">
    <div>
        <x-ui.title.h-2 label="{{ $title }}" class="mt-6" />
        <div
            class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md"
        >
            <form action="{{ route('order.store') }}" method="POST">
                @csrf
                <x-ui.form.input label="Телефон клиента" placeholder="Телефон клиента" required="required" type="tel" name="client[phone]" class="mb-4" id="client-phone" />

                <x-ui.form.input label="ID клиента" type="text" name="client[id]" class="hidden" id="client-id" />
                <x-ui.form.input label="Имя" placeholder="Имя" required="required" type="text" name="client[name]" class="mb-4 hidden" id="client-name" />
                <span id="client-archive" class="block mb-4 text-red-500 hidden">ВНИМАНИЕ! Клиент находится в архиве. Для создания заказа восстановите его.</span>
                <x-ui.form.select label="Площадка" placeholder="Площадка" required="required" :options="$fromPlatforms" name="client[from_platform_id]" class="mb-4 hidden" id="client-from-platform-id"/>
                <x-ui.form.textarea label="Комментарий" placeholder="Комментарий" name="client[comment]" class="hidden mb-4" id="client-comment"/>

                <x-ui.form.select label="Статус заказа" placeholder="Статус заказа" required="required" :options="$statuses" name="status" class="mb-4"/>
                <x-ui.form.input label="Дата выдачи" placeholder="Дата выдачи" required="required" type="datetime-local" name="date_issue" class="mb-4" />
                <x-ui.form.input label="Сумма заказа" placeholder="Сумма заказа" required="required" type="text" name="amount_total" class="mb-4" isAmount="true" />
                <x-ui.form.input label="Оплаченная сумма заказа" placeholder="Оплаченная сумма заказа" required="required" type="text" name="amount_payed" class="mb-4" isAmount="true" />
                <x-ui.form.select label="Товары" required="required" :options="$products" name="product_ids" multiple class="mb-4"/>
                <x-ui.form.textarea label="Описание заказа" placeholder="Описание заказа" required="required" name="description" class="mb-4" />

                <x-ui.form.button label="Создать" type="submit" class="mt-4 disabled:opacity-50 disabled:pointer-events-none" id="action-create-order" />
            </form>
        </div>
    </div>
</x-layouts.main>

<script>
    window.addEventListener('load', function() {
        let clientArchive = document.getElementById('client-archive');

        let phone = document.getElementById('client-phone').querySelector('input');
        let clientId = document.getElementById('client-id').querySelector('input');
        let name = document.getElementById('client-name').querySelector('input');
        let from = document.getElementById('client-from-platform-id').querySelector('select');
        let comment = document.getElementById('client-comment').querySelector('textarea');

        let btnSubmit = document.getElementById('action-create-order');

        if(phone) {
            phone.addEventListener('change', (ev) => {
                let phone = ev.target.value.replace(/\D/g, '');

                if(phone.length === 11) {
                    axios.post('/clients/findByPhone', {
                        phone: phone,
                    })
                        .then(function (response) {
                            let data = response.data;

                            name.value = data.name;
                            from.value = data.from_platform_id;
                            comment.value = data.comment;
                            clientId.value = data.id;

                            name.closest('.control-wrap').classList.remove('hidden');
                            from.closest('.control-wrap').classList.add('hidden');
                            comment.closest('.control-wrap').classList.add('hidden');

                            name.disabled = true;

                            if(data.deleted_at)
                            {
                                clientArchive.classList.remove('hidden');
                                btnSubmit.disabled = true;
                            } else {
                                clientArchive.classList.add('hidden');
                                btnSubmit.disabled = false;
                            }
                        })
                        .catch(function (error) {
                            name.value = null;
                            from.value = null;
                            comment.value = null;
                            clientId.value = null;

                            name.closest('.control-wrap').classList.remove('hidden');
                            from.closest('.control-wrap').classList.remove('hidden');
                            comment.closest('.control-wrap').classList.remove('hidden');

                            name.disabled = false;

                            clientArchive.classList.add('hidden');
                            btnSubmit.disabled = false;
                        })
                }
            });
        }
    });
</script>
