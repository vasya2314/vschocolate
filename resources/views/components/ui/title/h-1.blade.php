@props(['label'])

<h1
    {{ $attributes->merge(["class" => "mb-4 text-3xl font-semibold text-gray-700"]) }}
>
    {{ $label }}
</h1>
