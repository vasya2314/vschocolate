@php
    $title = "Редактирование платформы";
@endphp

<x-layouts.main title="{{ $title }}">
    <div>
        <x-ui.title.h-2 label="{{ $title }}" class="mt-6" />
        <div
            class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md"
        >
            <form action="{{ route('fromPlatform.update', $fromPlatform->id) }}" method="POST">
                @csrf
                @method('patch')
                <x-ui.form.input label="ID" placeholder="ID" type="text" name="id" class="mb-4" value="{{ $fromPlatform->id }}" disabled />
                <x-ui.form.input label="Имя" placeholder="Имя" required="required" type="text" name="name" class="mb-4" value="{{ $fromPlatform->name }}" />
                <x-ui.form.button label="Обновить" type="submit" class="mt-4" />
            </form>
        </div>
    </div>
</x-layouts.main>
