@php
    $title = "Создание клиента";
@endphp

<x-layouts.main title="{{ $title }}">
    <div>
        <x-ui.title.h-2 label="{{ $title }}" class="mt-6" />
        <div
            class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md"
        >
            <form action="{{ route('client.store') }}" method="POST">
                @csrf
                <x-ui.form.input label="Имя" placeholder="Имя" required="required" type="text" name="name" class="mb-4" />
                <x-ui.form.input label="Телефон" placeholder="Телефон" required="required" type="tel" name="phone" class="mb-4" />
                <x-ui.form.select label="Площадка" placeholder="Площадка" required="required" :options="$fromPlatforms" name="from_platform_id" class="mb-4"/>
                <x-ui.form.textarea label="Комментарий" placeholder="Комментарий" name="comment"/>

                <x-ui.form.button label="Создать" type="submit" class="mt-4" />
            </form>
        </div>
    </div>
</x-layouts.main>
