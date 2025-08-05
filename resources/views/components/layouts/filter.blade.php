@props([
    'action'
])

<!-- Filter Backdrop -->
<div
    x-show="isFilterOpen"
    x-transition:enter="transition ease-in-out duration-150"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in-out duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-20 flex items-end bg-[rgba(0,0,0,0.5)] sm:items-center sm:justify-center"
></div>

<!-- Filter Sidebar -->
<aside
    class="fixed inset-y-0 right-0 z-20 w-64 overflow-y-auto bg-white"
    x-show="isFilterOpen"
    x-transition:enter="transition ease-in-out duration-150"
    x-transition:enter-start="opacity-0 transform translate-x-20"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in-out duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0 transform translate-x-20"
    @click.away="closeFilter"
    @keydown.escape="closeFilter"
>
    <form action="{{ $action }}" class="p-4">
        <x-ui.title.h-3 label="Фильтр"/>
        {{ $slot }}
        <div class="mt-6">
            <x-ui.form.button label="Применить" type="submit" class="w-full"/>
        </div>
        <div class="mt-4">
            <x-ui.form.button-blue-a label="Сбросить" href="{{ $action }}"/>
        </div>
    </form>
</aside>
