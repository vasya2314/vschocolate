@php(
    $title = "Главная"
)

<x-layouts.main title="{{ $title }}">
    <div>
        <x-ui.title.h-2 label="{{ $title }}" class="mt-6" />
        <x-blocks.stats />
    </div>
</x-layouts.main>
